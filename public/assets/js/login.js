
$(function() {
    var newPhoneMask = function (phone, e, currentField, options) {
        if (phone.length < 15) {
            return '(00) 0000-00009';
        }
        return '(00) 00000-0009';
    };

    $("input[name='phone'], .phone-mask").mask(newPhoneMask, { onKeyPress: function (phone, e, currentField, options) {
        $(currentField).mask(newPhoneMask(phone), options);
    }
    });

    $('.select2').select2();
    $(".form-validate").validate();

    $("#login").submit(function(event) {

        event.preventDefault();

        var formData = {
            'username': $("input[name='username']").val(),
            'password': $("input[name='password']").val()
        }

        $('.modal-loading').fadeIn('slow');

        $.ajax({
            type: "POST",
            url: "/admin/login_check",
            data: formData,
            dataType: "json",
            success: function(data) {
                if (data.success) {
                    toastr.success(data.message);
                    $(location).attr("href", data.redirect);
                } else {
                    toastr.info(data.error);
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
                $('.modal-loading').fadeOut('slow');
            }

        });

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
        "positionClass": "toast-top-right",
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