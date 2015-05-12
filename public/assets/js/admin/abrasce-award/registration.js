/**
 * Registration JS
 *
 * @author Anderson Costa <arcostasi@gmail.com>
 */

/**
 * Reset form
 */
function resetForm() {

    $("input[name='id']").val("");

    $('#formRegistration').each(function() {
        this.reset();
    });
}

/**
 * Get url registers
 * @returns {url_getRegiter|String}
 */
function getUrlRegisters() {
    return url_getRegiter = "/admin/abrasce-award/registrations/" + _eventID + "/get-data";
}

/**
 * Get order
 * @returns {order|Int}
 */
function getOrderColumn() {
    return order_column = 6;
}

/**
 * Show datatable modal
 */
function showModalAwardRegistrationProject(fk_event){

    console.log(fk_event);

    $('#registration-project-modal').modal('show');
}

// Set menu options
$(".sidebar-menu").find("li.active").removeClass("active");
$(".abrasce-award-menu").addClass("active");
$(".registrations-link").addClass("active");

// Set selection option
$("#select-event").val(_eventID);
$("#select-event").change(function (e) {
    _loader.show();
    window.location.href = "/admin/abrasce-award/registrations/" + $(this).val();
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

    $.post('/admin/abrasce-award/registrations/' + _eventID + '/info', params, function (response) {

        if (response.success) {

            // Populate form data
            var data = response.data;
            $("input[name='id']").val(data.id);
            $("input[name='shopping']").val(data.shopping);
            $("input[name='project_title']").val(data.project_title);
            $("input[name='registration_number']").val(data.registration_number);
            $("input[name='invoice_number']").val(data.invoice_number);
            $("input[name='invoice_due_date']").val(data.invoice_due_date);
            $("input[name='invoice_payment_date']").val(data.invoice_payment_date);
            $("input[name='zip']").val(data.shopping_zip);
            $("input[name='address']").val(data.shopping_address);
            $("input[name='city']").val(data.shopping_city);
            $("#state").val(data.shopping_state);
            $("#status").val(data.status);
            $("input[name='phone']").val(data.shopping_phone);
            $("input[name='fax']").val(data.shopping_fax);
            $("input[name='responsible_document_number']").val(data.responsible_document_number);
            $("input[name='responsible_name']").val(data.responsible_name);
            $("input[name='responsible_position']").val(data.responsible_position);
            $("input[name='responsible_email']").val(data.responsible_email);
            $("input[name='administrator_name']").val(data.administrator_name);
            $("input[name='companies_shopping']").val(data.companies_shopping);
            $("input[name='billing_document_number']").val(data.billing_document_number);
            $("input[name='billing_name']").val(data.billing_name);
            $("input[name='billing_zip']").val(data.billing_zip);
            $("input[name='billing_address']").val(data.billing_address);
            $("input[name='billing_city']").val(data.billing_city);
            $("#billing_state").val(data.billing_state);
        }

    }).error(function (jqXHR, textStatus, errorThrown) {

        if (IsJsonString(jqXHR.responseText)) {
            var data = $.parseJSON(jqXHR.responseText);
            if (data.success == false) {
                toastr.error(data.error);
            }
        }

    }).complete(function (data) {
        $('#registration-modal').modal('show');
    });

});

// Submit form
$('#formRegistration').submit(function (event) {

    event.preventDefault();

    var url;

    if ($("input[name='id']").val() != "") {
        url = "/admin/abrasce-award/registrations/update";
    }

    var formData = $('#formRegistration').serializeArray();

    $.post(url, formData, function (data) {

        if (data.success) {
            toastr.success(data.message);
            setIdAction("");
            $('#registration-modal').modal('hide');
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