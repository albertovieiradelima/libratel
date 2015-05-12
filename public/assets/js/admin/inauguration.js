    function getUrlRegisters() {
        return url_getRegiter = "/admin/get-inaugurations";
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

        $('#formInauguration').each(function() {
            this.reset();
        });
    }

    $(".sidebar-menu").find("li.active").removeClass("active");
    $(".inaugurations-menu").addClass("active");
    $(".inaugurations-link").addClass("active");

    $('.input-group.date').datepicker({
        format: 'dd/mm/yyyy',
        autoclose: true,
        language: "pt-BR",
        todayHighlight: true,
        keyboardNavigation: false
    });

    $.ajax({
        type: "POST",
        url: "/admin/get-inaugurationcategory",
        dataType: "json",
        success: function(data) {
            if (data.data.length > 0) {
                $.each(data.data, function(key, category) {
                    if(category[2] === 'Ativo'){
                        $('#fk_inauguration_category').append('<option value="' + category[0] + '">' + category[1] + '</option>');
                    }
                });
            } else {
                toastr.warning("Nenhuma categoria foi encontrada. Certifique-se se as mesmas já foram cadastradas!");
                return;
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

    $('#formInauguration').submit(function(event) {

        event.preventDefault();

        var formData = new FormData($('#formInauguration')[0]);

        var url;
        if ($("input[name='id']").val() != "") {
            url = "/admin/inauguration/update";
            if ($(".file-caption-name").attr("title") != null) {
                formData.append("image", $(".file-caption-name").attr("title"));
            }
        } else {
            url = "/admin/inauguration/insert";
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
                    $('#inauguration-modal').modal('hide');
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
            url: "/admin/get-inaugurations",
            data: id,
            dataType: "json",
            success: function(data) {
                if (data.success) {
                    console.log(data.inauguration);
                    $("input[name='id']").val(data.inauguration.id);
                    $("input[name='shopping']").val(data.inauguration.shopping);
                    $("input[name='inauguration_date']").val(data.inauguration.inauguration_date);
                    $("input[name='abl']").val(data.inauguration.abl);
                    $("input[name='link']").val(data.inauguration.link);
                    $("input[name='city']").val(data.inauguration.city);
                    $("#state").val(data.inauguration.state);
                    $("#fk_inauguration_category").val(data.inauguration.fk_inauguration_category);

                    if (data.inauguration.image != "") {
                        $('#file').fileinput('refresh', {
                            'allowedFileExtensions': ['jpg', 'png', 'gif'],
                            'initialPreview': [
                                "<img src='/uploads/inaugurations/" + data.inauguration.image + "' class='file-preview-image'>",
                            ],
                            'overwriteInitial': true,
                            'maxFileSize': 500,
                            'initialCaption': data.inauguration.image
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

                    if (data.inauguration.status == "open") {
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
                $('#inauguration-modal').modal('show');
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
                    url: "/admin/inauguration/delete",
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
