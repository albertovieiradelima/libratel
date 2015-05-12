/**
 * Created by albertovieira on 4/1/15.
 */

function getUrlRegisters() {
    return url_getRegiter = "/admin/event-registration-participants/" + _eventID + "/get-data";
}

function resetForm() {
    $("input[name='id']").val("");

    $('#formClient').each(function () {
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
    window.location.href = "/admin/event-registration-participants/" + $(this).val();
});

$('#formEventRegistrationParticipants').submit(function (event) {

    event.preventDefault();

    var formData = new FormData($('#formEventRegistrationParticipants')[0]);

    $.ajax({
        type: "POST",
        url: "/admin/event-registration-participants/update",
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
                $('#event-registration-participants-modal').modal('hide');
                table.ajax.reload();
            }
        }

    });

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

    $.post('/admin/event-registration-participants/' + _eventID + '/get-data', params, function (response) {

        if (response.success) {
            // Populate form data
            var data = response.data;
            $("input[name='id']").val(data.id);
            $("input[name='certificate_name']").val(data.certificate_name);
            $("input[name='cpf']").val(data.cpf);
            $("input[name='email']").val(data.email);
            $("input[name='badge_name']").val(data.badge_name);
            $("input[name='badge_company']").val(data.badge_company);
            $("#job").select2("val", data.job);
            $("#area").select2("val", data.area);
            $("input[name='phone']").val(data.phone);
            $("#"+data.sex).iCheck('check');
        }

    }).error(function (jqXHR, textStatus, errorThrown) {

        if (IsJsonString(jqXHR.responseText)) {
            var data = $.parseJSON(jqXHR.responseText);
            if (data.success == false) {
                toastr.error(data.error);
            }
        }

    }).complete(function (data) {
        $('#event-registration-participants-modal').modal('show');
    });

});