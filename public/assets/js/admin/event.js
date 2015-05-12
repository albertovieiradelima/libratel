/**
 * Created by albertovieira on 4/12/14.
 */

function getUrlRegisters() {
    return url_getRegiter = "/admin/get-events";
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

    $('#formEvent').each(function () {
        this.reset();
    });
}

$(".sidebar-menu").find("li.active").removeClass("active");
$(".courses_events-menu").addClass("active");
$(".events-link").addClass("active");

$("#file").fileinput({
    'allowedFileExtensions': ['jpg', 'png', 'gif'],
    'overwriteInitial': true,
});

$(".starthour").timepicker({
    showInputs: false,
    showMeridian: false
});

$(".endhour").timepicker({
    showInputs: false,
    showMeridian: false
});

$(".dropdown-menu").css("left", "inherit");

$('#formEvent').submit(function (event) {

    event.preventDefault();

    var formData = new FormData($('#formEvent')[0]);

    var url;
    if ($("input[name='id']").val() != "") {
        url = "/admin/events/update";
        if ($(".file-caption-name").attr("title") != null) {
            formData.append("image", $(".file-caption-name").attr("title"));
        }
    } else {
        url = "/admin/events/insert";
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
                $('#event-modal').modal('hide');
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
        url: "/admin/get-events",
        data: id,
        dataType: "json",
        success: function (data) {
            if (data.success) {
                console.log(data.event);
                $("input[name='id']").val(data.event.id);
                $("input[name='title']").val(data.event.title);
                var description = $('#description').data("wysihtml5").editor;
                description.setValue(data.event.description);
                var cancellation_policy = $('#cancellation_policy').data("wysihtml5").editor;
                cancellation_policy.setValue(data.event.cancellation_policy);

                if (data.event.image != "") {
                    $('#file').fileinput('refresh', {
                        'allowedFileExtensions': ['jpg', 'png', 'gif'],
                        'initialPreview': [
                            "<img src='/uploads/events/" + data.event.image + "' class='file-preview-image' alt='The Moon' title='The Moon'>",
                        ],
                        'overwriteInitial': true,
                        'maxFileSize': 500,
                        'initialCaption': data.event.image
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

                $("input[name='startdate']").datepicker('update', data.event.start_date.replace(/\//g, '-'));
                $("input[name='enddate']").datepicker('update', data.event.end_date.replace(/\//g, '-'));
                $("input[name='starthour']").val(data.event.start_hour);
                $("input[name='endhour']").val(data.event.start_hour);
                $("input[name='local']").val(data.event.local);
                $("input[name='site']").val(data.event.site);
                $("input[name='number_vacancies']").val(data.event.number_vacancies);
                $("input[name='days_invoice']").val(data.event.days_invoice);

                if (data.event.exclusive_associated == "1") {
                    $("input[name='exclusive_associated']").iCheck('check');
                } else {
                    $("input[name='exclusive_associated']").iCheck('uncheck');
                }

                if (data.event.free_event == "1") {
                    $("input[name='free_event']").iCheck('check');
                } else {
                    $("input[name='free_event']").iCheck('uncheck');
                }

                if (data.event.status == "active") {
                    $("input[name='status']").iCheck('check');
                } else {
                    $("input[name='status']").iCheck('uncheck');
                }

                if (data.event.inscription == "active") {
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
            $('#event-modal').modal('show');
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
                url: "/admin/events/delete",
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
