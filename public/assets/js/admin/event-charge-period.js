/**
 * Created by albertovieira on 3/30/15.
 */

function getUrlRegisters() {
    return url_getRegiter = "/admin/event-charge-period/" + _eventID + "/get-data";
}

function resetForm() {
    $("input[name='id']").val("");

    $('#formEventChargePeriod').each(function() {
        this.reset();
    });
}

$(".sidebar-menu").find("li.active").removeClass("active");
$(".courses_events-menu").addClass("active");
$(".events-link").addClass("active");
$(".courses-link").addClass("active");

// Set selection option
$("#select-event").val(_eventID);
$("#select-event").change(function (e) {
    _loader.show();
    window.location.href = "/admin/event-charge-period/" + $(this).val();
});

// Submit form
$('#formEventChargePeriod').submit(function (event) {

    event.preventDefault();

    var url;

    if ($("input[name='id']").val() != "") {
        url = "/admin/event-charge-period/update";
    } else {
        url = "/admin/event-charge-period/insert";
    }

    var formData = $('#formEventChargePeriod').serializeArray();

    $.post(url, formData, function (data) {

        if (data.success) {
            setIdAction("");
            $('#event-charge-period-modal').modal('hide');
            toastr.success(data.message);
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

    $.post('/admin/event-charge-period/' + _eventID + '/get-data', params, function (response) {

        if (response.success) {
            // Populate form data
            var data = response.data;
            $("input[name='id']").val(data.id);
            $("input[name='fk_event']").val(data.fk_event);
            $("input[name='start_date']").val(data.start_date);
            $("input[name='end_date']").val(data.end_date);
            $("input[name='associated_price']").val(data.associated_price);
            $("input[name='standard_price']").val(data.standard_price);

        }

    }).error(function (jqXHR, textStatus, errorThrown) {

        if (IsJsonString(jqXHR.responseText)) {
            var data = $.parseJSON(jqXHR.responseText);
            if (data.success == false) {
                toastr.error(data.error);
            }
        }

    }).complete(function (data) {
        $('#event-charge-period-modal').modal('show');
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
                url: "/admin/event-charge-period/delete",
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