    function getUrlRegisters() {
        return url_getRegiter = "/admin/get-associates";
    }
    
    function resetForm() {

        $("input[name='id']").val("");

        $('#formAssociate').each(function() {
            this.reset();
        });
    }

    $(".sidebar-menu").find("li.active").removeClass("active");
    $(".contacts-menu").addClass("active");
    $(".associates-link").addClass("active");

    $('#formAssociate').submit(function(event) {

        event.preventDefault();

        var formData = new FormData($('#formAssociate')[0]);

        var url;
        if ($("input[name='id']").val() != "") {
            url = "/admin/associates/update";
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
                    $('#associate-modal').modal('hide');
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
            url: "/admin/get-associates",
            data: id,
            dataType: "json",
            success: function(data) {
                if (data.success) {
                    $("input[name='id']").val(data.associate.id);
                    $("input[name='name']").val(data.associate.name);
                    $("input[name='email']").val(data.associate.email);
                    $("input[name='phone']").val(data.associate.phone);
                    $("input[name='business']").val(data.associate.business);
                    var message = $('#message').data("wysihtml5").editor;
                    message.setValue(data.associate.message);
                    message.composer.disable();
                    $("input[name='date']").val(data.associate.date);

                    if (data.associate.status == "read") {
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
                $('#associate-modal').modal('show');
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
                    url: "/admin/associates/delete",
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
