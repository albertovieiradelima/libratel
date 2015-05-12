/**
 * Admin Boleto JS
 *
 * @author Anderson Costa <arcostasi@gmail.com>
 */

/**
 * Reset form
 */
function resetForm() {

    $("input[name='id']").val("");

    $('#formSetupBoleto').each(function() {
        this.reset();
    });
}

// Set menu options
$(".sidebar-menu").find("li.active").removeClass("active");
$(".setup-menu").addClass("active");
$(".boleto-link").addClass("active");

// Button edit on click
$('.edit').click(function (e) {

    resetForm();

    var params = {
        'id': 1
    };

    $.post('/admin/setup/boleto/edit', params, function (response) {

        if (response.success) {

            // Populate form data
            var data = response.data;
            $("input[name='id']").val(data.id);
            $("input[name='cedente_name']").val(data.cedente_name);
            $("input[name='cnpj']").val(data.cedente_cnpj);
            $("input[name='cedente_address']").val(data.cedente_address);
            $("input[name='cedente_zip']").val(data.cedente_zip);
            $("input[name='cedente_city']").val(data.cedente_city);
            $("input[name='cedente_state']").val(data.cedente_state);
            $("input[name='cedente_agencia']").val(data.cedente_agencia);
            $("input[name='cedente_agencia_dv']").val(data.cedente_agencia_dv);
            $("input[name='cedente_conta']").val(data.cedente_conta);
            $("input[name='cedente_conta_dv']").val(data.cedente_conta_dv);
            $("input[name='cedente_carteira']").val(data.cedente_carteira);
            $("input[name='cedente_label1']").val(data.cedente_label1);
            $("input[name='cedente_label2']").val(data.cedente_label2);
            $("input[name='cedente_label3']").val(data.cedente_label3);
            $("input[name='cedente_label4']").val(data.cedente_label4);
            $("input[name='cedente_label5']").val(data.cedente_label5);
            $("input[name='cedente_label6']").val(data.cedente_label6);
        }

    }).error(function (jqXHR, textStatus, errorThrown) {

        if (IsJsonString(jqXHR.responseText)) {
            var data = $.parseJSON(jqXHR.responseText);
            if (data.success == false) {
                toastr.error(data.error);
            }
        }

    }).complete(function (data) {
        $('#boleto-modal').modal('show');
    });

});

// Submit form
$('#formBoleto').submit(function (event) {

    event.preventDefault();

    var formData = $('#formBoleto').serializeArray();

    $.post('/admin/setup/boleto/update', formData, function (data) {

        if (data.success) {
            toastr.success(data.message);
            setIdAction("");
            $('#boleto-modal').modal('hide');
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