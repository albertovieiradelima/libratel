function getUrlRegisters() {
    return url_getRegiter = "/admin/get-courses";
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

    $('#formCourse').each(function () {
        this.reset();
    });
}

$(".sidebar-menu").find("li.active").removeClass("active");
$(".courses_events-menu").addClass("active");
$(".courses-link").addClass("active");

$(".starthour").timepicker({
    showInputs: false,
    showMeridian: false
});

$(".endhour").timepicker({
    showInputs: false,
    showMeridian: false
});

$(".dropdown-menu").css("left", "inherit");

$('#formCourse').submit(function (event) {

    event.preventDefault();

    var formData = new FormData($('#formCourse')[0]);

    var url;
    if ($("input[name='id']").val() != "") {
        url = "/admin/courses/update";
        if ($(".file-caption-name").attr("title") != null) {
            formData.append("image", $(".file-caption-name").attr("title"));
        }
    } else {
        url = "/admin/courses/insert";
    }

    $('.modal-loading').fadeIn('slow');

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
            $('.modal-loading').fadeOut('slow');

            var data = $.parseJSON(jqXHR.responseText);

            if (data.success == true) {
                setIdAction("");
                $('#course-modal').modal('hide');
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

    $('.modal-loading').fadeIn('slow');

    $.ajax({
        type: "POST",
        url: "/admin/get-courses",
        data: id,
        dataType: "json",
        success: function (data) {
            if (data.success) {
                console.log(data.course);
                $("input[name='id']").val(data.course.id);
                $("input[name='title']").val(data.course.title);
                var description = $('#description').data("wysihtml5").editor;
                description.setValue(data.course.description);
                var cancellation_policy = $('#cancellation_policy').data("wysihtml5").editor;
                cancellation_policy.setValue(data.course.cancellation_policy);

                if (data.course.image != "") {
                    $('#file').fileinput('refresh', {
                        'allowedFileExtensions': ['jpg', 'png', 'gif'],
                        'initialPreview': [
                            "<img src='/uploads/events/" + data.course.image + "' class='file-preview-image' alt='The Moon' title='The Moon'>",
                        ],
                        'overwriteInitial': true,
                        'maxFileSize': 500,
                        'initialCaption': data.course.image
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

                $("input[name='start_date']").datepicker('update', data.course.start_date.replace(/\//g, '-'));
                $("input[name='end_date']").datepicker('update', data.course.end_date.replace(/\//g, '-'));
                $("input[name='starthour']").val(data.course.start_hour);
                $("input[name='endhour']").val(data.course.start_hour);
                $("input[name='local']").val(data.course.local);
                $("input[name='site']").val(data.course.site);
                $("input[name='number_vacancies']").val(data.course.number_vacancies);
                $("input[name='days_invoice']").val(data.course.days_invoice);

                if (data.course.exclusive_associated == "1") {
                    $("input[name='exclusive_associated']").iCheck('check');
                } else {
                    $("input[name='exclusive_associated']").iCheck('uncheck');
                }

                if (data.course.free_event == "1") {
                    $("input[name='free_event']").iCheck('check');
                } else {
                    $("input[name='free_event']").iCheck('uncheck');
                }

                if (data.course.status == "active") {
                    $("input[name='status']").iCheck('check');
                } else {
                    $("input[name='status']").iCheck('uncheck');
                }

                if (data.course.inscription == "active") {
                    $("input[name='inscription']").iCheck('check');
                } else {
                    $("input[name='inscription']").iCheck('uncheck');
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
            $('.modal-loading').fadeOut('slow');
            $('#course-modal').modal('show');
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
                url: "/admin/courses/delete",
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
