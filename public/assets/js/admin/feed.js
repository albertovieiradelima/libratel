function getUrlRegisters() {
    return url_getRegiter = "/admin/get-feeds";
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

    $('#formFeed').each(function() {
        this.reset();
    });
}

$(".sidebar-menu").find("li.active").removeClass("active");
$(".feeds-menu").addClass("active");
$(".feeds-link").addClass("active");

$('#formFeed').submit(function(event) {

    event.preventDefault();

    var formData = new FormData($('#formFeed')[0]);

    var url;
    if ($("input[name='id']").val() != "") {

        url = "/admin/feeds/update";

        image = $('.fileImage').find('.file-caption-name');
        thumb = $('.fileThumb').find('.file-caption-name');

        if (image.attr("title") != null) {
            formData.append("image", image.attr("title"));
        }
        
        if (thumb.attr("title") != null) {
            formData.append("thumb", thumb.attr("title"));
        }
    } else {
        url = "/admin/feeds/insert";
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
                $('#feed-modal').modal('hide');
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
        url: "/admin/get-feeds",
        data: id,
        dataType: "json",
        success: function(data) {
            if (data.success) {
                console.log(data.feed);
                $("input[name='id']").val(data.feed.id);
                $("input[name='title']").val(data.feed.title);
                var description = $('#description').data("wysihtml5").editor;
                description.setValue(data.feed.description);

                if (data.feed.thumb != "" && data.feed.thumb != null) {
                    $('#thumb').fileinput('refresh', {
                        'allowedFileExtensions': ['jpg', 'png', 'gif'],
                        'initialPreview': [
                            "<img src='/uploads/feeds/" + data.feed.thumb + "' class='file-preview-image'>",
                        ],
                        'overwriteInitial': true,
                        'maxFileSize': 500,
                        'initialCaption': data.feed.thumb
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

                if (data.feed.image != "" && data.feed.image != null) {
                    $('#file').fileinput('refresh', {
                        'allowedFileExtensions': ['jpg', 'png', 'gif'],
                        'initialPreview': [
                            "<img src='/uploads/feeds/" + data.feed.image + "' class='file-preview-image'>",
                        ],
                        'overwriteInitial': true,
                        'maxFileSize': 500,
                        'initialCaption': data.feed.image
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

                $("input[name='date']").val(data.feed.date);
                if (data.feed.status == "active") {
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
            $('#feed-modal').modal('show');
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
                url: "/admin/feeds/delete",
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
