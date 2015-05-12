    function getUrlRegisters() {
        return url_getRegiter = "/admin/get-store";
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

        $('#formStore').each(function() {
            this.reset();
        });
    }

    $(".sidebar-menu").find("li.active").removeClass("active");
    $(".store-menu").addClass("active");
    $(".store-link").addClass("active");

    $('.input-group.date').datepicker({
        format: 'yyyy',
        autoclose: true,
        startView: 2,
        minViewMode: 2,
        language: "pt-BR",
        todayHighlight: true,
        keyboardNavigation: false
    });

    $.ajax({
        type: "POST",
        url: "/admin/get-storecategory",
        dataType: "json",
        success: function(data) {

            if (data.data.length > 0) {
                $.each(data.data, function(key, category) {
                    $('#fk_store_category').append('<option value="' + category[0] + '">' + category[1] + '</option>');
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

    $('#formStore').submit(function(event) {

        event.preventDefault();

        var formData = new FormData($('#formStore')[0]);

        var url;
        if ($("input[name='id']").val() != "") {
            url = "/admin/store/update";
            if ($(".file-caption-name").attr("title") != null) {
                formData.append("image", $(".file-caption-name").attr("title"));
            }
        } else {
            url = "/admin/store/insert";
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
                    $('#store-modal').modal('hide');
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
            url: "/admin/get-store",
            data: id,
            dataType: "json",
            success: function(data) {
                if (data.success) {
                    console.log(data.store);
                    $("input[name='id']").val(data.store.id);
                    $("input[name='title']").val(data.store.title);
                    $("input[name='year']").val(data.store.year);
                    $("input[name='price']").val(data.store.price);
                    $("#fk_store_category").val(data.store.fk_store_category);
                    var sinopse = $('#sinopse').data("wysihtml5").editor;
                    sinopse.setValue(data.store.sinopse);

                    if (data.store.image != "") {
                        $('#file').fileinput('refresh', {
                            'allowedFileExtensions': ['jpg', 'png', 'gif'],
                            'initialPreview': [
                                "<img src='/uploads/store/" + data.store.image + "' class='file-preview-image'>",
                            ],
                            'overwriteInitial': true,
                            'maxFileSize': 500,
                            'initialCaption': data.store.image
                        });
                    }

                    if (data.store.status == "active") {
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
                $('#store-modal').modal('show');
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
                    url: "/admin/store/delete",
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
