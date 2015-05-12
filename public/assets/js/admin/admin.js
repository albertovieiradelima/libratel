/**
 * Created by albertovieiradelima on 15/11/14.
 */
var table;
var _selectedID;

function ltrim(str) {
    return str.replace(/^\s+/, "");
}

function rtrim(str) {
    return str.replace(/\s+$/, "");
}

$.fn.clearForm = function() {
    return this.each(function() {
        var type = this.type, tag = this.tagName.toLowerCase();
        if (tag == 'form')
            return $(':input',this).clearForm();
        if (type == 'text' || type == 'password' || tag == 'textarea')
            this.value = '';
        else if (type == 'checkbox' || type == 'radio')
            this.checked = false;
        else if (tag == 'select')
            this.selectedIndex = -1;
    });
};

$(document).ready(function() {

    var newPhoneMask = function (phone, e, currentField, options) {
        if (phone.length < 15) {
            return '(00) 0000-00009';
        }
        return '(00) 00000-0009';
    };
    
    // Jquery validate
    $(".form-validate").validate();

    $("input[name='cpf_cnpj']").mask("999.999.999-99", {"placeholder": "000.000.000-00"});
    $("input[name='cnpj']").mask("99.999.999/9999-99", {"placeholder": "00.000.000/0000-00"});
    $("input[name='cpf']").mask("999.999.999-99", {"placeholder": "000.000.000-00"});
    $("input[name='date']").mask("99/99/9999", {"placeholder": "00/00/0000"});
    $("input[name='phone']").mask(newPhoneMask, { onKeyPress: function (phone, e, currentField, options) {
            $(currentField).mask(newPhoneMask(phone), options);
        }
    });
    $("input[name='cep']").mask("99999-999", {"placeholder": "00000-000"});
    $(".money").maskMoney();
    $(".mask-number").mask("9999999999");
    $('.mask-decimal').mask("###0.0", {reverse: true});
    
    $('.input-group.date').datepicker({
        format: 'dd/mm/yyyy',
        language: 'pt-BR',
        todayHighlight: true,
        autoclose: true
    });

    $('.date-time-picker').datetimepicker({
        format: 'dd/mm/yyyy hh:ii'
    });

    $('.input-daterange').datepicker({
        format: 'dd/mm/yyyy',
        language: 'pt-BR',
        autoclose: true
    });

    $('.select2').select2();

    $("#type_person").change(function(){
        if($("#type_person").val() == 1){
            $("input[name='cpf_cnpj']").mask("999.999.999-99", {"placeholder": "000.000.000-00"});
        } else {
            $("input[name='cpf_cnpj']").mask("99.999.999/9999-99", {"placeholder": "00.000.000/0000-00"});
        }
    });

    $('.textarea').wysihtml5({
        'html': true, //Button which allows you to edit the generated HTML. Default false
        'image': true, //Button to insert an image. Default true,
        'color': true, //Button to change color of font 
        'locale': "pt-BR",
        'stylesheets': ["/assets/css/wysiwyg-color.css"]
    });

    $('#registers tfoot th').each(function() {
        var title = $('#registers thead th').eq($(this).index()).text();
        title = ltrim(title);
        title = rtrim(title);
        $(this).html('<input type="text" placeholder="Filtrar ' + title + '" />');
    });

    if ((typeof getUrlRegisters == 'function')) {

        _order = 1;

        if ((typeof getOrderColumn == 'function')){
            if(getOrderColumn()) {
                _order = getOrderColumn();
            }
        }

        table = $('#registers').DataTable({
            "order": [
                [_order, "asc"]
            ],
            "language": {
                "lengthMenu": "Exibir _MENU_ registros por página",
                "zeroRecords": "Desculpe, nenhum registro encontrado.",
                "info": "Exibindo _START_ a _END_ de _TOTAL_",
                "infoEmpty": "Desculpe, nenhum registro encontrado.",
                "search": "Filtrar"
            },
            "ajax": {
                "url": getUrlRegisters(),
                "type": "POST"
            }
        });

        table.columns().eq(0).each(function(colIdx) {
            $('input', table.column(colIdx).footer()).on('keyup change', function() {
                table.column(colIdx)
                    .search(this.value)
                    .draw();
            });
        });

        var tableTools = new $.fn.dataTable.TableTools(table, {
            "aButtons": [{
                "sExtends": "copy",
                "sButtonText": "Copiar"
            }, {
                "sExtends": "csv",
                "sButtonText": "CSV"
            }, {
                "sExtends": "xls",
                "sButtonText": "XLS"
            }, {
                "sExtends": "pdf",
                "sButtonText": "PDF"
            }, {
                "sExtends": "print",
                "sButtonText": "Imprimir"
            }],
            "sSwfPath": "/assets/swf/copy_csv_xls_pdf.swf"
        });

        $(tableTools.fnContainer()).insertAfter('div.info-datatable');
    }


    $('#registers tbody').on('click', 'tr', function() {
        if ($(this).hasClass('active')) {
            $(this).removeClass('active');
        } else {
            table.$('tr.active').removeClass('active');
            $(this).addClass('active');
        }

        var id = $(this).children('td:first').text();
        if (id == $(".edit").attr("id")) {
            setIdAction("");
        } else {
            setIdAction(id);
        }

    });

    function IsJsonString(str) {
        try {
            JSON.parse(str);
        } catch (e) {
            return false;
        }
        return true;
    }

    // Toast Messenger config
    toastr.options = {
        "closeButton": false,
        "debug": false,
        "positionClass": "toast-bottom-right",
        "onclick": null,
        "showDuration": "300",
        "hideDuration": "1000",
        "timeOut": "3000",
        "extendedTimeOut": "1000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
    }

    // Loader hide
    $('.modal-loading').fadeOut('slow');

});


// Set the classes that TableTools uses to something suitable for Bootstrap
$.extend(true, $.fn.DataTable.TableTools.classes, {
    "container": "btn-group",
    "buttons": {
        "normal": "btn btn-primary",
        "disabled": "btn btn-primary disabled"
    },
    "collection": {
        "container": "btn btn-primary dropdown-toggle pull-right",
        "buttons": {
            "normal": "",
            "disabled": "disabled"
        }
    }
});

// Have the collection use a bootstrap compatible dropdown
$.extend(true, $.fn.DataTable.TableTools.DEFAULTS.oTags, {
    "collection": {
        "container": "ul",
        "button": "li",
        "liner": "a"
    }
});

function setIdAction(id) {
    var id = id;
    $(".edit").attr("id", id);
    $(".remove").attr("id", id);
    _selectedID = id;
}

// Global spin
var _spinObj = function() {

    // Private
    var _removeSpinner = function() {
        $('body').remove("div#crmall-spin");
    };
    
    var _addSpinner = function() {
        _removeSpinner();
        $('body').append(_spin);
    };

    var _spin = "<div id='crmall-spin' style='position:fixed; width:120px; height:120px; left:50%; top:50%; margin-left:-60px; margin-top:-60px; background:rgba(0,0,0,0.5); display:block; z-index:200000000; border-radius: 10px;'></div>";

    return {
        show: function() {
            if($('#crmall-spin').length <= 0){
                _addSpinner();
            }
            $('#crmall-spin').spin('large','#FFF');
            $('#crmall-spin').fadeIn(200);
        },
        hide: function() {
            $('#crmall-spin').fadeOut(200);
        }
    };

};

// Init Spin
var _loader = _spinObj();

// Global ajax events
$(document).ajaxStart(function(){
    _loader.show();
});

$(document).ajaxError(function(){
    _loader.hide();
});

$(document).ajaxComplete(function(){
    _loader.hide();
});
