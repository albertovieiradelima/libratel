/**
 * Created by Renato Peterman <renato.peterman@crmall.com> on 30/01/2015.
 */

var _fkEvent;
var _formAction;
var _urlAwardEventList = "/admin/abrasce-award/award-event/list";

/**
 * Reset form
 */
function _resetAwardFieldForm(){
    $('#file-logo, #file-banner').fileinput('refresh', {
        'allowedFileExtensions': ['jpg', 'png', 'gif'],
        'initialPreview': [],
        'overwriteInitial': true,
        'maxFileSize': 500,
        'initialCaption': ""
    });
    $('#formAwardEvent div').removeClass('has-error');
    $('#formAwardEvent').each(function() {
        this.reset();
    });
}

/**
 * Define input filters for datatable
 */
$('#award-event-registers tfoot th').each(function() {
    var title = $('#award-event-registers thead th').eq($(this).index()).text();
    title = ltrim(title);
    title = rtrim(title);
    $(this).html('<input type="text" placeholder="Filtrar ' + title + '" />');
});

/**
 * Initialize datatable
 */
var _tableAwardEvent;

/**
 * Show datatable modal
 */
function showModalAwardEvent(fk_event){

    _fkEvent = fk_event;
    var urlList = _urlAwardEventList + '/' + _fkEvent;

    if(!_tableAwardEvent || _tableAwardEvent == undefined){

        _tableAwardEvent = $('#award-event-registers').DataTable({
            "order": [
                [1, "asc"]
            ],
            "language": {
                "lengthMenu": "Exibir _MENU_ registros por página",
                "zeroRecords": "Desculpe, nenhum registro encontrado.",
                "info": "Exibindo _START_ a _END_ de _TOTAL_",
                "infoEmpty": "Desculpe, nenhum registro encontrado.",
                "search": "Filtrar"
            },
            "ajax": {
                "url": urlList,
                "type": "POST"
            }
        });

    }else{
        _tableAwardEvent.ajax.url(urlList).load();
    }

    $('#modal-award-event').modal('show');
}

/**
 * Show award event modal form
 * @param fk_award
 * @param fk_event
 */
function showModalAwardEventForm(fk_award, fk_event){

    _resetAwardFieldForm();

    // Update
    if(fk_award != undefined &&  fk_event != undefined){

        _formAction = 'edit';

        $.post('/admin/abrasce-award/award-event/list', {fk_award:fk_award, fk_event:fk_event}, function(response){

            if(!response.success){
                toastr.error(data.message);
                return;
            }

            var data = response.data;

            // Populate form
            $('#formAwardEvent select[name=fk_award]').val(data.fk_award);
            $('#formAwardEvent input[name=fk_event]').val(data.fk_event);
            $('#formAwardEvent input[name=title]').val(data.title);
            $('#formAwardEvent input[name=billing_days_to_due]').val(data.billing_days_to_due);
            $('#formAwardEvent input[name=registration_price]').val(data.registration_price);

            // Date setup
            var dateBegin = moment(data.registration_date_begin);
            var dateEnd = moment(data.registration_date_end);
            $('#formAwardEvent input[name=registration_date_begin]').val(dateBegin.format('DD/MM/YYYY HH:mm'));
            $('#formAwardEvent input[name=registration_date_end]').val(dateEnd.format('DD/MM/YYYY HH:mm'));

            // Logo
            if (data.logo != "") {
                $('#file-logo').fileinput('refresh', {
                    'allowedFileExtensions': ['jpg', 'png', 'gif'],
                    'initialPreview': ["<img src='/uploads/award-event/" +data.logo + "' class='file-preview-image'>",],
                    'overwriteInitial': true,
                    'maxFileSize': 500,
                    'initialCaption': data.logo
                });
            }

            // Banner
            if (data.banner != "") {
                $('#file-banner').fileinput('refresh', {
                    'allowedFileExtensions': ['jpg', 'png', 'gif'],
                    'initialPreview': ["<img src='/uploads/award-event/" +data.banner + "' class='file-preview-image'>",],
                    'overwriteInitial': true,
                    'maxFileSize': 500,
                    'initialCaption': data.banner
                });
            }

            var description = $('#description-award-event').data("wysihtml5").editor;
            description.setValue(data.description);

            // Show
            $('#modal-award-event-form').modal('show');

        }).fail(function(jqXHR, textStatus){

            toastr.error("Ocorreu um erro ao executar a solicitação: " + textStatus);

        });

    }else{ // New

        _formAction = 'new';
        _resetAwardFieldForm();

        $('input[name=fk_event]').val(_fkEvent);

        $('#modal-award-event-form').modal('show');

    }

}

/**
 * Remove award event
 */

function removeAwardEvent(fk_award, fk_event){

    if(fk_award == undefined ||  fk_event == undefined){
        toastr.error("ID inválido");
        return;
    }

    bootbox.confirm("Deseja realmente excluir este registro?", function(result) {

        if (result === true) {

            var params = {
                fk_award: fk_award,
                fk_event: fk_event
            };

            $.post("/admin/abrasce-award/award-event/delete", params, function(data){

                if (data.success) {
                    toastr.success(data.message);
                    _tableAwardEvent.ajax.reload();
                } else {
                    toastr.error(data.message);
                }

            }).error(function(jqXHR,textStatus,errorThrown){

                if (IsJsonString(jqXHR.responseText)) {
                    var data = $.parseJSON(jqXHR.responseText);
                    if (data.success == false) {
                        toastr.error(data.error);
                    }
                }

            }).always(function(data){
                // always
            });

        }

    });

}

/**
 * Submit form
 */
// Define form validation
var _formAwardEvent = $("#formAwardEvent").validate({

    submitHandler: function(form) {

        var url;

        if (_formAction == 'edit') {
            url = "/admin/abrasce-award/award-event/update";
        } else {
            url = "/admin/abrasce-award/award-event/insert";
        }

        // var formData = $('#formAwardEvent').serializeArray();
        var formData = new FormData($('#formAwardEvent')[0]);

        $.ajax({
            type: "POST",
            url: url,
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function(data) {
                if (data.success) {
                    toastr.success(data.message);
                } else {
                    toastr.error(data.message);
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
            complete: function(jqXHR) {
                var data = $.parseJSON(jqXHR.responseText);

                if (data.success == true) {
                    $('#modal-award-event-form').modal('hide');
                    _tableAwardEvent.ajax.reload();
                }
            }

        });
    }
});