function getUrlRegisters() {
    return url_getRegiter = "/admin/get-clients";
}

function resetForm() {

    $("input[name='id']").val("");

    $('#formClient').each(function () {
        this.reset();
    });
}

$(".sidebar-menu").find("li.active").removeClass("active");
$(".clients-link").addClass("active");

$('#formClient').submit(function (event) {

    event.preventDefault();

    var formData = new FormData($('#formClient')[0]);

    var url;
    if ($("input[name='id']").val() != "") {
        url = "/admin/client/update";
    } else {
        url = "/admin/client/insert";
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
                $('#client-modal').modal('hide');
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
        url: "/admin/get-clients",
        data: id,
        dataType: "json",
        success: function (data) {
            if (data.success) {
                $("input[name='id']").val(data.client.id);
                $("input[name='name']").val(data.client.name);
                $("input[name='nickname']").val(data.client.nickname);
                $("input[name='email']").val(data.client.email);
                $("input[name='cpf_cnpj']").val(data.client.cpf_cnpj);
                $("input[name='rg_ie']").val(data.client.rg_ie);
                $("input[name='cep']").val(data.client.cep);
                $("input[name='address']").val(data.client.address);
                $("input[name='number']").val(data.client.number);
                $("input[name='complement']").val(data.client.complement);
                $("input[name='neighborhood']").val(data.client.neighborhood);
                $("input[name='city']").val(data.client.city);
                $("#state").val(data.client.state);
                $("input[name='phone']").val(data.client.phone);
                $("input[name='date']").val(data.client.date);
                $("#type_person").val(data.client.type);

                if (data.client.status == "active") {
                    $("input[name='status']").iCheck('check');
                } else {
                    $("input[name='status']").iCheck('uncheck');
                }

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
            $('#client-modal').modal('show');
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
                url: "/admin/client/delete",
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