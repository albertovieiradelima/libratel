/**
 * Created by albertovieira on 4/2/15.
 */

function getUrlRegisters() {
    return url_getRegiter = "/admin/event-registration/" + _eventID + "/get-data";
}

function resetForm() {
    $("input[name='id']").val("");

    $('#formEventRegistration').each(function () {
        this.reset();
    });

    var dateNow = $.datepicker.formatDate("dd/mm/yy", new Date());
    $("input[name='invoice_payment_date']").val(dateNow);
}

$(".sidebar-menu").find("li.active").removeClass("active");
$(".courses_events-menu").addClass("active");
$(".events-link").addClass("active");
$(".courses-link").addClass("active");

// Set selection option
$("#select-event").val(_eventID);
$("#select-event").change(function (e) {
    _loader.show();
    window.location.href = "/admin/event-registration/" + $(this).val();
});

function cancelEventRegistration(id) {

    if (!id) {
        toastr.error("Selecione um registro!");
        return;
    }

    bootbox.confirm("Deseja realmente cancelar este registro?", function(result) {

        if (result === true) {

            var params = {
                'id': id,
                'status': 1
            };

            $.post('/admin/event-registration/update', params, function (response) {

                if (response.success) {
                    toastr.success(response.message);
                } else {
                    toastr.error(response.message);
                }

            }).error(function (jqXHR, textStatus, errorThrown) {

                if (IsJsonString(jqXHR.responseText)) {
                    var data = $.parseJSON(jqXHR.responseText);
                    if (data.success == false) {
                        toastr.error(data.error);
                    }
                }

            }).complete(function (data) {
                table.ajax.reload();
            });
        }

    });

}

function confirmEventRegistration(id) {

    resetForm();

    if (!id) {
        toastr.error("Selecione um registro!");
        return;
    }

    $("input[name='id']").val(id);
    $("input[name='status']").val(2);
    $('#event-registration-modal').modal('show');
}

function newInvoice(id) {
    resetForm();

    if (!id) {
        toastr.error("Selecione um registro!");
        return;
    }

    $.ajax({
        type: "GET",
        dataType: "json",

        url: '/admin/event-registration/register/'+id,
        success: function(data) {
            $('#invoice_payment_date').val(data.data.dueDate);
            $('#invoice_email').val(data.data.email);
        },
        error: function() {
            toastr.error(data);
        },
        complete: function(){
            $('#event-new-invoice-modal').modal('show');
        }
    });

    $("input[name='id']").val(id);
    $("input[name='status']").val(2);
}

$('#formEventRegistration').submit(function (event) {

    event.preventDefault();

    var formData = $('#formEventRegistration').serializeArray();

    $.post('/admin/event-registration/update', formData, function (response) {

        if (response.success) {
            toastr.success(response.message);
        } else {
            toastr.error(response.message);
        }

    }).error(function (jqXHR, textStatus, errorThrown) {

        if (IsJsonString(jqXHR.responseText)) {
            var data = $.parseJSON(jqXHR.responseText);
            if (data.success == false) {
                toastr.error(data.error);
            }
        }

    }).complete(function (data) {
        $('#event-registration-modal').modal('hide');
        table.ajax.reload();
    });

});

$('#formEventNewInvoice').submit(function (event) {
    event.preventDefault();

    var formData = $('#formEventNewInvoice').serializeArray();

    $.post('/admin/event-registration/resend-boleto/', formData, function (response) {
        if (response.success) {
            toastr.success(response.message);
            $('#event-new-invoice-modal').modal('hide');
        } else {
            toastr.error(response.message);
        }
    }).error(function (jqXHR, textStatus, errorThrown) {
        if (IsJsonString(jqXHR.responseText)) {
            var data = $.parseJSON(jqXHR.responseText);
            if (data.success == false) {
                toastr.error(data.error);
            }
        }
    }).complete(function (data) {

    });
});