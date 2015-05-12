/**
 * Created by albertovieiradelima on 03/12/14.
 */

$(function() {

    var newPhoneMask = function (phone, e, currentField, options) {
        if (phone.length < 15) {
            return '(00) 0000-00009';
        }
        return '(00) 00000-0009';
    };

    $("input[name='CPF']").mask("999.999.999-99", {"placeholder": "000.000.000-00"});
    $("input[name='cpf_cnpj'], .cpf").mask("999.999.999-99", {"placeholder": "000.000.000-00"});
    $("input[name='date'], .date-mask").mask("99/99/9999", {"placeholder": "00/00/0000"});
    $("input[name='phone'], .phone-mask").mask(newPhoneMask, { onKeyPress: function (phone, e, currentField, options) {
            $(currentField).mask(newPhoneMask(phone), options);
        }
    });
    $("input[name='cep'], .cep-mask").mask("99999-999", {"placeholder": "00000-000"});
    $('.input-group.date').datepicker({
        format: 'dd/mm/yyyy',
        language: 'pt-BR',
        autoclose: true
    });

    $(".number-mask").mask("99999999999999");

    // Jquery validate
    $(".form-validate").validate();
    $('.select2').select2();

    function resetForm(form) {

        $(form).each(function() {
            this.reset();
        });
    }

    function validateEmail(email) {
        var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return re.test(email);
    }

    $('#submit-newsletter').click(function(event) {

        if (validateEmail($("input[name='email']").val()) == true) {

            var formData = {
                'email': $("input[name='email']").val()
            }

            console.log(formData);

            $.ajax({
                type: "POST",
                url: '/site/newsletter',
                data: formData,
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
                    $('.modal-loading').fadeOut('slow');

                    var data = $.parseJSON(jqXHR.responseText);

                    if (data.success == true) {
                        $("input[name='email']").val("");
                    }
                }

            });

        } else {
            toastr.error('E-mail informado é inválido.');
        }

    });

    $('#formAboutus').submit(function(event) {

        event.preventDefault();

        var formData = new FormData($('#formAboutus')[0]);

        url = "/site/fale-com-abrasce/insert";
        
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
                    console.log(data);

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
                    resetForm('#formAboutus');
                }
            }

        });

    });

    $('#formAssociete').submit(function(event) {

        event.preventDefault();

        var formData = new FormData($('#formAssociete')[0]);

        url = "/site/seja-associado/insert";
        
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
                    console.log(data);

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
                    resetForm('#formAssociete');
                }
            }

        });

    });

    responseMonitor();

    function responseMonitor() {
        var tam = $(window).width();

        if (tam < 992) {
            $('.navbar-brand').css('marginTop', '-15px');
            $('.navbar-brand img').attr('src', '/assets/img/logo-min.jpg');
            $('.btn-sinopse').attr('data-placement', 'bottom');
        } else {
            $('.navbar-brand').css('margin-top', '-30px');
            $('.navbar-brand img').attr('src', '/assets/img/logo.jpg');
            $('.btn-sinopse').attr('data-placement', 'right');
        }
    }

    var tam = $(window).width();

    $(window).scroll(function() {

        if (tam >= 992) {
            if ($(this).scrollTop() > 120) {
                if ($('.navbar-brand img').attr('src') !== '/assets/img/logo-min.jpg') {
                    $('.navbar-brand').css('display', 'none');
                    $('.navbar-brand').css('marginTop', '-15px');
                    $('.navbar-brand img').attr('src', '/assets/img/logo-min.jpg');
                    $('.navbar-brand').fadeIn("slow");
                }
            } else {
                if ($('.navbar-brand img').attr('src') !== '/assets/img/logo.jpg') {
                    $('.navbar-brand').css('display', 'none');
                    $('.navbar-brand').css('margin-top', '-30px');
                    $('.navbar-brand img').attr('src', '/assets/img/logo.jpg');
                    $('.navbar-brand').fadeIn("slow");
                }
            }
        }
    });

});

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