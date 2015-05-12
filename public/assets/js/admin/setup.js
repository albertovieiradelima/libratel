/**
 * Admin Setup JS
 *
 * @author Anderson Costa <arcostasi@gmail.com>
 */

/**
 * Reset form
 */
function resetForm() {

    $("input[name='id']").val("");

    $('#formSetupSmtp').each(function() {
        this.reset();
    });
}

// Set menu options
$(".sidebar-menu").find("li.active").removeClass("active");
$(".setup-menu").addClass("active");
$(".smtp-link").addClass("active");

// Button edit on click
$('.edit').click(function (e) {

    resetForm();

    var params = {
        'id': 1
    };

    $.post('/admin/setup/smtp/edit', params, function (response) {

        if (response.success) {

            // Populate form data
            var data = response.data;
            $("input[name='id']").val(data.id);
            $("input[name='smtp_host']").val(data.smtp_host);
            $("input[name='smtp_port']").val(data.smtp_port);
            $("input[name='smtp_user']").val(data.smtp_user);
            $("input[name='smtp_pass']").val(data.smtp_pass);
            $("input[name='smtp_email']").val(data.smtp_email);
            $("input[name='smtp_name']").val(data.smtp_name);
        }

    }).error(function (jqXHR, textStatus, errorThrown) {

        if (IsJsonString(jqXHR.responseText)) {
            var data = $.parseJSON(jqXHR.responseText);
            if (data.success == false) {
                toastr.error(data.error);
            }
        }

    }).complete(function (data) {
        $('#smtp-modal').modal('show');
    });

});

// Submit form
$('#formSMTP').submit(function (event) {

    event.preventDefault();

    var formData = $('#formSMTP').serializeArray();

    $.post('/admin/setup/smtp/update', formData, function (data) {

        if (data.success) {
            toastr.success(data.message);
            setIdAction("");
            $('#smtp-modal').modal('hide');
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