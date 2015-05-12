$.validator.setDefaults({
    highlight: function(element) {
        $(element).closest('.form-group').addClass('has-error');
        //$('#emptyErrorAlert').slideDown();
    },
    unhighlight: function(element) {
        $(element).closest('.form-group').removeClass('has-error');
        //$('#emptyErrorAlert').slideUp();
    },
    errorElement: 'span',
    errorClass: 'help-block',
    errorPlacement: function(error, element) {
        if (element.parent('.input-group').length) {
            error.insertAfter(element.parent());
        } else {
            error.insertAfter(element);
        }
    }
});