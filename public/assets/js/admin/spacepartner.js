function getUrlRegisters() {
    return url_getRegiter = "/admin/get-spacepartners";
}

function resetForm() {

    $('#file').fileinput('refresh', {
        'allowedFileExtensions': ['jpg', 'png', 'gif'],
        'initialPreview': [],
        'overwriteInitial': true,
        'maxFileSize': 500,
        'initialCaption': ""
    });

    $('#thumb').fileinput('refresh', {
        'allowedFileExtensions': ['jpg', 'png', 'gif'],
        'initialPreview': [],
        'overwriteInitial': true,
        'maxFileSize': 100,
        'initialCaption': ""
    });

    $("input[name='id']").val("");

    $('#formSpacePartner').each(function () {
        this.reset();
    });
}


$(".sidebar-menu").find("li.active").removeClass("active");
$(".feeds-menu").addClass("active");
$(".spacepartners-link").addClass("active");

$('#formSpacePartner').submit(function (event) {

    event.preventDefault();

    var formData = new FormData($('#formSpacePartner')[0]);

    var url;
    if ($("input[name='id']").val() != "") {

        url = "/admin/spacepartners/update";

        image = $('.fileImage').find('.file-caption-name');
        thumb = $('.fileThumb').find('.file-caption-name');

        if (image.attr("title") != null) {
            formData.append("image", image.attr("title"));
        }

        if (thumb.attr("title") != null) {
            formData.append("thumb", thumb.attr("title"));
        }
    } else {
        url = "/admin/spacepartners/insert";
    }

    $.ajax({
        type: "POST",
        url: url,
        data: formData,
        cache: false,
        contentType: false,
        processData: false,
        dataType: "json",
        success: function (data) {
            if (data.success) {
                toastr.success(data.message);
            } else {
                toastr.error(data.message);
            }
        },
        error: function (jqXHR, ajaxOptions, thrownError) {
            if (IsJsonString(jqXHR.responseText)) {
                var data = $.parseJSON(jqXHR.responseText);

                if (data.success == false) {
                    toastr.error(data.error);
                }
            }
        },
        complete: function (jqXHR) {
            var data = $.parseJSON(jqXHR.responseText);

            if (data.success == true) {
                setIdAction("");
                $('#spacepartner-modal').modal('hide');
                table.ajax.reload();
            }
        }

    });

});

$('.new').click(function (e) {
    resetForm();
});

$('.edit').click(function (e) {

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
        url: "/admin/get-spacepartners",
        data: id,
        dataType: "json",
        success: function (data) {
            if (data.success) {
                console.log(data.spacepartner);
                $("input[name='id']").val(data.spacepartner.id);
                $("input[name='title']").val(data.spacepartner.title);
                var description = $('#description').data("wysihtml5").editor;
                description.setValue(data.spacepartner.description);

                if (data.spacepartner.thumb != "" && data.spacepartner.thumb != null) {
                    $('#thumb').fileinput('refresh', {
                        'allowedFileExtensions': ['jpg', 'png', 'gif'],
                        'initialPreview': [
                            "<img src='/uploads/feeds/" + data.spacepartner.thumb + "' class='file-preview-image'>",
                        ],
                        'overwriteInitial': true,
                        'maxFileSize': 500,
                        'initialCaption': data.spacepartner.thumb
                    });
                } else {
                    $('#thumb').fileinput('refresh', {
                        'allowedFileExtensions': ['jpg', 'png', 'gif'],
                        'initialPreview': [],
                        'overwriteInitial': true,
                        'maxFileSize': 500,
                        'initialCaption': ""
                    });

                }

                if (data.spacepartner.image != "") {
                    $('#file').fileinput('refresh', {
                        'allowedFileExtensions': ['jpg', 'png', 'gif'],
                        'initialPreview': [
                            "<img src='/uploads/feeds/" + data.spacepartner.image + "' class='file-preview-image' alt='The Moon' title='The Moon'>",
                        ],
                        'overwriteInitial': true,
                        'maxFileSize': 500,
                        'initialCaption': data.spacepartner.image
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

                $("input[name='date']").val(data.spacepartner.date);
                if (data.spacepartner.status == "active") {
                    $("input[name='status']").iCheck('check');
                } else {
                    $("input[name='status']").iCheck('uncheck');
                }

            } else {
                toastr.error(data.message);
            }
        },
        error: function (jqXHR, ajaxOptions, thrownError) {
            if (IsJsonString(jqXHR.responseText)) {
                var data = $.parseJSON(jqXHR.responseText);

                if (data.success == false) {
                    toastr.error(data.error);
                }
            }
        },
        complete: function () {
            $('#spacepartner-modal').modal('show');
        }

    });

});

$('.remove').click(function (e) {

    if (!$(this).attr("id")) {
        toastr.error("Selecione um registro!");
        return;
    }
    var id = {
        'id': $(this).attr("id")
    }

    bootbox.confirm("Deseja realmente excluir este registro?", function (result) {

        if (result === true) {

            $('.modal-loading').fadeIn('slow');

            $.ajax({
                type: "POST",
                url: "/admin/spacepartners/delete",
                data: id,
                dataType: "json",
                success: function (data) {
                    if (data.success) {
                        setIdAction("");
                        toastr.success(data.message);
                    } else {
                        toastr.error(data.message);
                    }
                },
                error: function (jqXHR, ajaxOptions, thrownError) {
                    if (IsJsonString(jqXHR.responseText)) {
                        var data = $.parseJSON(jqXHR.responseText);

                        if (data.success == false) {
                            toastr.error(data.error);
                        }
                    }
                },
                complete: function () {
                    $('.modal-loading').fadeOut('slow');
                    table.ajax.reload();
                }

            });
        }

    });

});
