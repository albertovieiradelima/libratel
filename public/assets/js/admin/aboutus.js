    function getUrlRegisters() {
        return url_getRegiter = "/admin/get-aboutus";
    }

    function resetForm() {
        $('#file').fileinput('refresh', {
            'allowedFileExtensions': ['jpg', 'png', 'gif'],
            'initialPreview': [],
            'overwriteInitial': true,
            'maxFileSize': 500,
            'initialCaption': ""
        });

        $("input[name='id']").val("");
        $("input[name='title']").val("");
//        $('#fk_aboutus_type option').remove();

        $('#formAboutus').each(function() {
            this.reset();
        });
    }

    $(".sidebar-menu").find("li.active").removeClass("active");
    $(".aboutus-link").addClass("active");

    $("#file").fileinput({
        'allowedFileExtensions': ['jpg', 'png', 'gif'],
        'overwriteInitial': true,
    });

    $('#formAboutus').submit(function(event) {

        event.preventDefault();

        var formData = new FormData($('#formAboutus')[0]);

        var url;
        if ($("input[name='id']").val() != "") {
            url = "/admin/aboutus/update";
            if($(".file-caption-name").attr("title") != null){
                formData.append("image", $(".file-caption-name").attr("title"));
            }
        } else {
            url = "/admin/aboutus/insert";
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
                    $('#aboutus-modal').modal('hide');
                    table.ajax.reload();
                }
            }

        });

    });

    $('.new').click(function(e) {

        resetForm();

        $('#aboutus-modal').modal('show');

//        $.ajax({
//            type: "POST",
//            url: "/admin/get-aboutus-types",
//            dataType: "json",
//            success: function(data) {
//                if (data.success) {
//                    if(data.aboutus_types.length > 0){
//                        $.each(data.aboutus_types, function(key, type) {
//                            $('#fk_aboutus_type').append('<option value="' + type.id + '">' + type.title + '</option>');
//                        });
//
//                    } else {
//                        toastr.warning("Todas as categorias de Sobre a Abrasce já foram cadastradas!");
//                        $('#aboutus-modal').modal('hide');
//                        return;
//                    }
//
//                } else {
//                    toastr.error(data.message);
//                }
//            },
//            error: function(jqXHR, ajaxOptions, thrownError) {
//                if (IsJsonString(jqXHR.responseText)) {
//                    var data = $.parseJSON(jqXHR.responseText);
//
//                    if (data.success == false) {
//                        toastr.error(data.error);
//                    }
//                }
//            },
//            complete: function() {
//
//            }
//        });

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
            url: "/admin/get-aboutus",
            data: id,
            dataType: "json",
            success: function(data) {
                if (data.success) {
                    $("input[name='id']").val(data.aboutus.id);
                    $("input[name='title']").val(data.aboutus.title);
                    var description = $('#description').data("wysihtml5").editor;
                    description.setValue(data.aboutus.description);
//                    $('#fk_aboutus_type').append('<option value="' + data.aboutus.fk_aboutus_type + '">' + data.aboutus.title + '</option>');

                    if (data.aboutus.image != "") {
                        $('#file').fileinput('refresh', {
                            'allowedFileExtensions': ['jpg', 'png', 'gif'],
                            'initialPreview': [
                                "<img src='/uploads/aboutus/" + data.aboutus.image + "' class='file-preview-image'>",
                            ],
                            'overwriteInitial': true,
                            'maxFileSize': 500,
                            'initialCaption': data.aboutus.image
                        });
                    } else {
                        $('#file').fileinput('refresh', {
                            'allowedFileExtensions': ['jpg', 'png', 'gif'],
                            'initialPreview': [],
                            'overwriteInitial': true,
                            'maxFileSize': 500,
                            'initialCaption': ""
                        });
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
                $('#aboutus-modal').modal('show');
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
                    url: "/admin/aboutus/delete",
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
