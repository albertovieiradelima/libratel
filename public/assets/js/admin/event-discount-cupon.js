/**
 * Created by albertovieira on 3/30/15.
 */
function getUrlRegisters() {
    return url_getRegiter = "/admin/event-discount-coupon/get-data";
}

function resetForm() {

    $("input[name='id']").val("");

    $('#formClient').each(function () {
        this.reset();
    });
}

$(".sidebar-menu").find("li.active").removeClass("active");
$(".courses_events-menu").addClass("active");
$(".event-discount-coupon-link").addClass("active");

$.ajax({
    type: "POST",
    url: "/admin/event-discount-coupon/get-event-data",
    dataType: "json",
    success: function(data) {
        if (data.length > 0) {
            $.each(data, function(key, event) {
                $('#fk_event').append('<option value="' + event['id'] + '">' + event['title'] + '</option>');
            });
        } else {
            toastr.warning("Nenhum evento foi encontrado. Certifique-se se as mesmos já foram cadastrados!");
            return;
        }

    },
    error: function(jqXHR, ajaxOptions, thrownError) {
        if (IsJsonString(jqXHR.responseText)) {
            var data = $.parseJSON(jqXHR.responseText);

            if (data.success == false) {
                toastr.error(data.message);
            }
        }
    },
    complete: function() {

    }
});

$('#formEventDiscountCoupon').submit(function (event) {

    event.preventDefault();

    var formData = new FormData($('#formEventDiscountCoupon')[0]);

    var url;
    if ($("input[name='id']").val() != "") {
        url = "/admin/event-discount-coupon/update";
    } else {
        url = "/admin/event-discount-coupon/insert";
    }

    $.ajax({
        type: "POST",
        url: url,
        data: formData,
        cache: false,
        contentType: false,
        processData: false,
        dataType: "json",
        success: function (data) {
            if (data.success) {
                toastr.success(data.message);
            } else {
                toastr.error(data.message);
            }
        },
        error: function (jqXHR, ajaxOptions, thrownError) {
            if (IsJsonString(jqXHR.responseText)) {
                var data = $.parseJSON(jqXHR.responseText);

                if (data.success == false) {
                    toastr.error(data.error);
                }
            }
        },
        complete: function (jqXHR) {

            var data = $.parseJSON(jqXHR.responseText);

            if (data.success == true) {
                setIdAction("");
                $('#event-discount-coupon-modal').modal('hide');
                table.ajax.reload();
            }
        }

    });

});

$('.new').click(function (e) {
    resetForm();
});

$('.edit').click(function (e) {

    resetForm();

    if (!$(this).attr("id")) {
        toastr.error("Selecione um registro!");
        return;
    }
    var id = {
        'id': $(this).attr("id")
    }

    $.ajax({
        type: "POST",
        url: "/admin/event-discount-coupon/get-data",
        data: id,
        dataType: "json",
        success: function (data) {
            if (data.success) {
                $("input[name='id']").val(data.coupon.id);
                $("#fk_event").select2("val", data.coupon.fk_event);
                $("input[name='company']").val(data.coupon.company);
                $("input[name='email']").val(data.coupon.email);
                $("input[name='contact']").val(data.coupon.contact);
                $("input[name='minimum_number']").val(data.coupon.minimum_number);
                $("input[name='maximum_number']").val(data.coupon.maximum_number);
                $("input[name='discount_participant']").val(data.coupon.discount_participant);
                $("input[name='expiration_date']").val(data.coupon.expiration_date);
                var observations = $('#observations').data("wysihtml5").editor;
                observations.setValue(data.coupon.observations);
                console.log($("#fk_event").val());
            } else {
                toastr.error(data.message);
            }
        },
        error: function (jqXHR, ajaxOptions, thrownError) {
            if (IsJsonString(jqXHR.responseText)) {
                var data = $.parseJSON(jqXHR.responseText);

                if (data.success == false) {
                    toastr.error(data.error);
                }
            }
        },
        complete: function () {
            $('#event-discount-coupon-modal').modal('show');
        }

    });

});

$('.remove').click(function (e) {

    if (!$(this).attr("id")) {
        toastr.error("Selecione um registro!");
        return;
    }
    var id = {
        'id': $(this).attr("id")
    }

    bootbox.confirm("Deseja realmente excluir este registro?", function (result) {

        if (result === true) {

            $.ajax({
                type: "POST",
                url: "/admin/event-discount-coupon/delete",
                data: id,
                dataType: "json",
                success: function (data) {
                    if (data.success) {
                        setIdAction("");
                        toastr.success(data.message);
                    } else {
                        toastr.error(data.message);
                    }
                },
                error: function (jqXHR, ajaxOptions, thrownError) {
                    if (IsJsonString(jqXHR.responseText)) {
                        var data = $.parseJSON(jqXHR.responseText);

                        if (data.success == false) {
                            toastr.error(data.error);
                        }
                    }
                },
                complete: function () {
                    table.ajax.reload();
                }

            });
        }

    });

});

$('#CEP').blur(function () {
    if ($('#CEP').val() != '') {
        $('#CEP').addClass('ajax-loader');
        // busca cep
        $.ajax({
            type: "GET",
            dataType: "json",
            url: 'http://www.crmall.com.br/interface_crmall/appcrmall/php/enderecoPorCep.php?identificador=tqyG1HzR2Q&cep={' + $('#CEP').val() + '}',
            success: function(data) {
                $('input[name=address]').val( data['logradouro'] );
                $('input[name=neighborhood]').val( data['bairro'] );
                $('input[name=city]').val( data['cidade'] );
                $('select[name=state]').val( data['uf'] );
                $('#CEP').removeClass('ajax-loader');

                $('#number').focus();
            },
            error: function() {
                $('#CEP').removeClass('ajax-loader');
            }
        });
    }
});