    function getUrlRegisters() {
        return url_getRegiter = "/admin/get-banners";
    }

    function resetForm() {

        $('#file').fileinput('refresh', {
            'allowedFileExtensions': ['jpg', 'png', 'gif'],
            'initialPreview': [],
            'overwriteInitial': true,
            'maxFileSize': 1000,
            'initialCaption': ""
        });

        $("input[name='id']").val("");

        $('#formBanner').each(function() {
            this.reset();
        });
    }

    $(".sidebar-menu").find("li.active").removeClass("active");
    $(".general-menu").addClass("active");
    $(".layout-submenu").addClass("active");
    $(".banners-link").addClass("active");

    $('#formBanner').submit(function(event) {

        event.preventDefault();

        var formData = new FormData($('#formBanner')[0]);

        var url;
        if ($("input[name='id']").val() != "") {
            url = "/admin/banner/update";
            if ($(".file-caption-name").attr("title") != null) {
                formData.append("image", $(".file-caption-name").attr("title"));
            }
        } else {
            url = "/admin/banner/insert";
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
                    $('#banner-modal').modal('hide');
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
            url: "/admin/get-banners",
            data: id,
            dataType: "json",
            success: function(data) {
                if (data.success) {
                    $("input[name='id']").val(data.banner.id);
                    $("input[name='link']").val(data.banner.link);
                    $("input[name='title']").val(data.banner.title);
                    $("input[name='description']").val(data.banner.description);

                    if (data.banner.image != "") {
                        $('#file').fileinput('refresh', {
                            'allowedFileExtensions': ['jpg', 'png', 'gif'],
                            'initialPreview': ["<img src='/uploads/banners/" + data.banner.image + "' class='file-preview-image'>",],
                            'overwriteInitial': true,
                            'maxFileSize': 500,
                            'initialCaption': data.banner.image
                        });
                    } 

                    if (data.banner.status == "active") {
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
                $('#banner-modal').modal('show');
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
                    url: "/admin/banner/delete",
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
