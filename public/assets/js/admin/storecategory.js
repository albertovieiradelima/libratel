    function getUrlRegisters() {
        return url_getRegiter = "/admin/get-storecategory";
    }
    
    function resetForm() {

        $("input[name='id']").val("");

        $('#formStoreCategory').each(function() {
            this.reset();
        });
    }


    $(".sidebar-menu").find("li.active").removeClass("active");
    $(".store-menu").addClass("active");
    $(".storecategories-link").addClass("active");

    $('#formStoreCategory').submit(function(event) {

        event.preventDefault();

        var formData = new FormData($('#formStoreCategory')[0]);

        var url;
        if ($("input[name='id']").val() != "") {
            url = "/admin/storecategory/update";
        } else {
            url = "/admin/storecategory/insert";
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
                    $('#storecategory-modal').modal('hide');
                    table.ajax.reload();
                }
            }

        });

    });

    $('.new').click(function(e) {

        resetForm();

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
            url: "/admin/get-storecategory",
            data: id,
            dataType: "json",
            success: function(data) {
                if (data.success) {
                    $("input[name='id']").val(data.storecategory.id);
                    $("input[name='name']").val(data.storecategory.name);

                    if (data.storecategory.status == "active") {
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
                $('#storecategory-modal').modal('show');
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
                    url: "/admin/storecategory/delete",
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
