/**
 * Created by albertovieiradelima on 30/01/15.
 */

/**
 * Get url registers
 * @returns {url_getRegiter|String}
 */
function getUrlRegisters() {
    return url_getRegiter = "/admin/abrasce-award/event-sponsor/" + _eventID + "/get-data";
}

/**
 * Reset form
 */
function resetForm() {

    $('#order option').remove();

    $.ajax({
        type: "POST",
        url: "/admin/abrasce-award/get-event-sponsor-orders/" + _eventID,
        dataType: "json",
        success: function(data) {
            if (data.orders.qtde.length > 0) {
                for (i = 1; i <= (parseInt(data.orders.qtde) + 1); i++) {
                    $('#order').append('<option value="' + i + '">' + i + 'º</option>');
                }
            } else {
                $('#order').append('<option value="1">1º</option>');
            }
        },
        error: function(jqXHR, ajaxOptions, thrownError) {
            if (IsJsonString(jqXHR.responseText)) {
                var data = $.parseJSON(jqXHR.responseText);

                if (data.success == false) {
                    toastr.error(data.error);
                }
            }
        },
        complete: function() {

        }
    });


    $("input[name='id']").val("");

    $('#formEventSponsor').each(function() {
        this.reset();
    });
}

$.ajax({
    type: "POST",
    url: "/admin/abrasce-award/get-sponsorcategory",
    dataType: "json",
    success: function(data) {

        if (data.data.length > 0) {
            $.each(data.data, function(key, category) {
                if(category[2] == "Ativo"){
                    $('#fk_sponsor_category').append('<option value="' + category[0] + '">' + category[1] + '</option>');
                }
            });
        } else {
            toastr.warning("Nenhuma categoria foi encontrada. Certifique-se se as mesmas já foram cadastradas!");
            return;
        }

    },
    error: function(jqXHR, ajaxOptions, thrownError) {
        if (IsJsonString(jqXHR.responseText)) {
            var data = $.parseJSON(jqXHR.responseText);
            if (data.success == false) {
                toastr.error(data.error);
            }
        }
    },
    complete: function() {

    }
});

$.ajax({
    type: "POST",
    url: "/admin/abrasce-award/get-sponsors",
    dataType: "json",
    success: function(data) {

        if (data.data.length > 0) {
            $.each(data.data, function(key, category) {
                if(category[3] == "Ativo"){
                    $('#fk_sponsor').append('<option value="' + category[0] + '">' + category[1] + '</option>');
                }
            });
        } else {
            toastr.warning("Nenhuma categoria foi encontrada. Certifique-se se as mesmas já foram cadastradas!");
            return;
        }

    },
    error: function(jqXHR, ajaxOptions, thrownError) {
        if (IsJsonString(jqXHR.responseText)) {
            var data = $.parseJSON(jqXHR.responseText);
            if (data.success == false) {
                toastr.error(data.error);
            }
        }
    },
    complete: function() {

    }
});

// Set menu options
$(".sidebar-menu").find("li.active").removeClass("active");
$(".abrasce-award-menu").addClass("active");
$(".events-link").addClass("active");

// Set selection option
$("#select-event").val(_eventID);
$("#select-event").change(function (e) {
    _loader.show();
    window.location.href = "/admin/abrasce-award/event-sponsor/" + $(this).val();
});

// Submit form
$('#formEventSponsor').submit(function (event) {

    event.preventDefault();

    var url;

    if ($("input[name='id']").val() != "") {
        url = "/admin/abrasce-award/event-sponsor/update";
    } else {
        url = "/admin/abrasce-award/event-sponsor/insert";
    }

    var formData = $('#formEventSponsor').serializeArray();

    $.post(url, formData, function (data) {

        if (data.success) {
            setIdAction("");
            $('#event-sponsor-modal').modal('hide');
            table.ajax.reload();
        } else {
            toastr.error(data.message);
        }

    }).error(function (jqXHR, textStatus, errorThrown) {

        if (IsJsonString(jqXHR.responseText)) {
            var data = $.parseJSON(jqXHR.responseText);
            if (data.success == false) {
                toastr.error(data.error);
            }
        }

    }).always(function (data) {

    });

});

// Button new on click
$('.new').click(function (e) {
    resetForm();
    $("input[name='fk_event']").val(_eventID);
});

// Button edit on click
$('.edit').click(function (e) {

    resetForm();

    if (!$(this).attr("id")) {
        toastr.error("Selecione um registro!");
        return;
    }

    var params = {
        'id': $(this).attr("id")
    };

    $.post('/admin/abrasce-award/event-sponsor/' + _eventID + '/get-data', params, function (response) {

        if (response.success) {

            // Populate form data
            var data = response.data;
            $("input[name='id']").val(data.id);
            $("input[name='fk_event']").val(data.fk_event);
            $("select[name='fk_sponsor']").val(data.fk_sponsor);
            $("select[name='fk_sponsor_category']").val(data.fk_sponsor_category);
            $("select[name='order']").val(data.order);

        }

    }).error(function (jqXHR, textStatus, errorThrown) {

        if (IsJsonString(jqXHR.responseText)) {
            var data = $.parseJSON(jqXHR.responseText);
            if (data.success == false) {
                toastr.error(data.error);
            }
        }

    }).complete(function (data) {
        $('#order option:last').remove();
        $('#event-sponsor-modal').modal('show');
    });

});

// Button remove on click
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
                url: "/admin/abrasce-award/event-sponsor/delete",
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
