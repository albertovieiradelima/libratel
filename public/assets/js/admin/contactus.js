    function getUrlRegisters() {
        return url_getRegiter = "/admin/get-contactus";
    }
    
    function resetForm() {

        $("input[name='id']").val("");

        $('#formContactus').each(function() {
            this.reset();
        });
    }

    $(".sidebar-menu").find("li.active").removeClass("active");
    $(".contacts-menu").addClass("active");
    $(".contactus-link").addClass("active");

    $('#formContactus').submit(function(event) {

        event.preventDefault();

        var formData = new FormData($('#formContactus')[0]);

        var url;
        if ($("input[name='id']").val() != "") {
            url = "/admin/contactus/update";
        }

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
                    setIdAction("");
                    $('#contactus-modal').modal('hide');
                    table.ajax.reload();
                }
            }

        });

    });

    $('.edit').click(function(e) {

        resetForm();

        if (!$(this).attr("id")) {
            toastr.error("Selecione um registro!");
            return;
        }
        var id = {
            'id': $(this).attr("id")
        }

        $.ajax({
            type: "POST",
            url: "/admin/get-contactus",
            data: id,
            dataType: "json",
            success: function(data) {
                if (data.success) {
                    $("input[name='id']").val(data.contactus.id);
                    $("input[name='name']").val(data.contactus.name);
                    $("input[name='email']").val(data.contactus.email);
                    $("input[name='area']").val(data.contactus.area);
                    $("input[name='subject']").val(data.contactus.subject);
                    $("input[name='business']").val(data.contactus.business);
                    $("input[name='job']").val(data.contactus.job);
                    $("input[name='cep']").val(data.contactus.cep);
                    $("input[name='address']").val(data.contactus.address);
                    $("input[name='number']").val(data.contactus.number);
                    $("input[name='complement']").val(data.contactus.complement);
                    $("input[name='neighborhood']").val(data.contactus.neighborhood);
                    $("input[name='city']").val(data.contactus.city);
                    $("input[name='state']").val(data.contactus.state);
                    $("input[name='phone']").val(data.contactus.phone);
                    var message = $('#message').data("wysihtml5").editor;
                    message.setValue(data.contactus.message);
                    message.composer.disable();
                    $("input[name='date']").val(data.contactus.date);

                    if (data.contactus.status == "read") {
                        $("input[name='status']").iCheck('check');
                    } else {
                        $("input[name='status']").iCheck('uncheck');
                    }

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
            complete: function() {
                $('#contactus-modal').modal('show');
            }

        });

    });

    $('.remove').click(function(e) {

        if (!$(this).attr("id")) {
            toastr.error("Selecione um registro!");
            return;
        }
        var id = {
            'id': $(this).attr("id")
        }

        bootbox.confirm("Deseja realmente excluir este registro?", function(result) {

            if (result === true) {

                $.ajax({
                    type: "POST",
                    url: "/admin/contactus/delete",
                    data: id,
                    dataType: "json",
                    success: function(data) {
                        if (data.success) {
                            setIdAction("");
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
                    complete: function() {
                        table.ajax.reload();
                    }

                });
            }

        });

    });
