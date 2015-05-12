    function getUrlRegisters() {
        return url_getRegiter = "/admin/get-topimages";
    }

    function resetForm() {

        $('#order option').remove();

        $.ajax({
            type: "POST",
            url: "/admin/get-topimage-orders",
            dataType: "json",
            success: function(data) {
                if (data.orders.qtde.length > 0) {
                    for (i = 1; i <= (parseInt(data.orders.qtde) + 1); i++) {
                        $('#order').append('<option value="' + i + '">' + i + 'º</option>');
                    }
                } else {
                    $('#order').append('<option value="1">1º</option>');
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

            }
        });

        $('#file').fileinput('refresh', {
            'allowedFileExtensions': ['jpg', 'png', 'gif'],
            'initialPreview': [],
            'overwriteInitial': true,
            'maxFileSize': 1000,
            'initialCaption': ""
        });

        $("input[name='id']").val("");

        $('#formTopImage').each(function() {
            this.reset();
        });
    }

    $(".sidebar-menu").find("li.active").removeClass("active");
    $(".general-menu").addClass("active");
    $(".layout-submenu").addClass("active");
    $(".topimages-link").addClass("active");

    $('#formTopImage').submit(function(event) {

        event.preventDefault();

        var formData = new FormData($('#formTopImage')[0]);

        var url;
        if ($("input[name='id']").val() != "") {
            url = "/admin/topimage/update";
            if ($(".file-caption-name").attr("title") != null) {
                formData.append("image", $(".file-caption-name").attr("title"));
            }
        } else {
            url = "/admin/topimage/insert";
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
                    $('#topimage-modal').modal('hide');
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

        var id = {'id': $(this).attr("id")};

        $.ajax({
            type: "POST",
            url: "/admin/get-topimages",
            data: id,
            dataType: "json",
            success: function(data) {
                if (data.success) {
                    $("input[name='id']").val(data.topimage.id);
                    $("input[name='link']").val(data.topimage.link);
                    $("input[name='title']").val(data.topimage.title);
                    $("input[name='description']").val(data.topimage.description);
                    $("#order").val(data.topimage.order);

                    if (data.topimage.image != "") {
                        $('#file').fileinput('refresh', {
                            'allowedFileExtensions': ['jpg', 'png', 'gif'],
                            'initialPreview': ["<img src='/uploads/banners/" + data.topimage.image + "' class='file-preview-image'>",],
                            'overwriteInitial': true,
                            'maxFileSize': 1000,
                            'initialCaption': data.topimage.image
                        });
                    } 

                    if (data.topimage.status == "active") {
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
                $('#order option:last').remove();
                $('#topimage-modal').modal('show');
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
        };

        bootbox.confirm("Deseja realmente excluir este registro?", function(result) {

            if (result === true) {

                $.ajax({
                    type: "POST",
                    url: "/admin/topimage/delete",
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
