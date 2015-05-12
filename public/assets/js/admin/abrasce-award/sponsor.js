/**
 * Created by albertovieiradelima on 27/01/15.
 */
function getUrlRegisters() {
    return url_getRegiter = "/admin/abrasce-award/get-sponsors";
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

    $('#formSponsor').each(function() {
        this.reset();
    });
}

$(".sidebar-menu").find("li.active").removeClass("active");
$(".abrasce-award-menu").addClass("active");
$(".award-sponsors-submenu").addClass("active");
$(".award-sponsors-link").addClass("active");

$('#formSponsor').submit(function(event) {

    event.preventDefault();

    var formData = new FormData($('#formSponsor')[0]);

    var url;
    if ($("input[name='id']").val() != "") {
        url = "/admin/abrasce-award/sponsor/update";
        if ($(".file-caption-name").attr("title") != null) {
            formData.append("image", $(".file-caption-name").attr("title"));
        }
    } else {
        url = "/admin/abrasce-award/sponsor/insert";
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
                $('#sponsor-modal').modal('hide');
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
        url: "/admin/abrasce-award/get-sponsors",
        data: id,
        dataType: "json",
        success: function(data) {
            if (data.success) {
                $("input[name='id']").val(data.sponsor.id);
                $("input[name='link']").val(data.sponsor.link);
                $("input[name='name']").val(data.sponsor.name);
                //$("#fk_sponsor_category").val(data.sponsor.fk_sponsor_category);

                var description = $('#description').data("wysihtml5").editor;
                description.setValue(data.sponsor.description);

                if (data.sponsor.logo != "") {
                    $('#file').fileinput('refresh', {
                        'allowedFileExtensions': ['jpg', 'png', 'gif'],
                        'initialPreview': ["<img src='/uploads/sponsors/" + data.sponsor.logo + "' class='file-preview-image'>",],
                        'overwriteInitial': true,
                        'maxFileSize': 500,
                        'initialCaption': data.sponsor.logo
                    });
                }

                if (data.sponsor.status == "active") {
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
            $('#sponsor-modal').modal('show');
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
                url: "/admin/abrasce-award/sponsor/delete",
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
