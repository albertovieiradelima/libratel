function getUrlRegisters() {
    return url_getRegiter = "/admin/get-magazines";
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

    $('#formMagazine').each(function() {
        this.reset();
    });
}

$(".sidebar-menu").find("li.active").removeClass("active");
$(".magazines-link").addClass("active");

$('#formMagazine').submit(function(event) {

    event.preventDefault();

    var formData = new FormData($('#formMagazine')[0]);

    var url;
    if ($("input[name='id']").val() != "") {
        url = "/admin/magazine/update";
        if ($(".file-caption-name").attr("title") != null) {
            formData.append("image", $(".file-caption-name").attr("title"));
        }
    } else {
        url = "/admin/magazine/insert";
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
                $('#magazine-modal').modal('hide');
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
        url: "/admin/get-magazines",
        data: id,
        dataType: "json",
        success: function(data) {
            if (data.success) {
                console.log(data.magazine);
                $("input[name='id']").val(data.magazine.id);
                $("input[name='publication']").val(data.magazine.publication);
                $("input[name='title']").val(data.magazine.title);

                var sinopse = $('#sinopse').data("wysihtml5").editor;
                var description = $('#description').data("wysihtml5").editor;
                sinopse.setValue(data.magazine.sinopse);
                description.setValue(data.magazine.description);

                if (data.magazine.image != "") {
                    $('#file').fileinput('refresh', {
                        'allowedFileExtensions': ['jpg', 'png', 'gif'],
                        'initialPreview': [
                            "<img src='/uploads/magazines/" + data.magazine.image + "' class='file-preview-image'>",
                        ],
                        'overwriteInitial': true,
                        'maxFileSize': 500,
                        'initialCaption': data.magazine.image
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

                if (data.magazine.status == "active") {
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
            $('#magazine-modal').modal('show');
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
                url: "/admin/magazine/delete",
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
