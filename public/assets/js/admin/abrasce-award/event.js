/**
 * Created by albertovieiradelima on 2/12/15.
 *
 * Event JS
 *
 * @author Alberto Vieira de Lima <albertovieiradelima@gmail.com>
 */

function getUrlRegisters() {
    return url_getRegiter = "/admin/abrasce-award/get-events";
}

function resetForm() {
    $("input[name='id']").val("");

    $('#formEvent').each(function () {
        this.reset();
    });
}

$(".sidebar-menu").find("li.active").removeClass("active");
$(".abrasce-award-menu").addClass("active");
$(".award-events-submenu").addClass("active");
$(".award-events-link").addClass("active");

$('.input-group.date').datepicker({
    format: 'yyyy',
    autoclose: true,
    startView: 2,
    minViewMode: 2,
    language: "pt-BR",
    todayHighlight: true,
    keyboardNavigation: false
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
        url = "/admin/abrasce-award/event/update";
    } else {
        url = "/admin/abrasce-award/event/insert";
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

    $.ajax({
        type: "POST",
        url: "/admin/abrasce-award/get-events",
        data: id,
        dataType: "json",
        success: function (data) {
            if (data.success) {
                console.log(data.event);
                $("input[name='id']").val(data.event.id);
                $("input[name='title']").val(data.event.title);
                var description = $('#description').data("wysihtml5").editor;
                description.setValue(data.event.description);

                $("input[name='start_date']").val(data.event.start_date);
                $("input[name='end_date']").val(data.event.end_date);
                $("input[name='year']").val(data.event.year);

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

            $.ajax({
                type: "POST",
                url: "/admin/abrasce-award/event/delete",
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
                    table.ajax.reload();
                }

            });
        }

    });

});

// Edit fields on click
$('.edit-sponsors').click(function (e) {

    if (!_selectedID) {
        toastr.error("Selecione um registro!");
        return;
    }

    window.location.href = '/admin/abrasce-award/event-sponsor/' + _selectedID;

});


// Functions table buttons

function showModalEventSponsor() {

    $('#modal-event-award').modal('show');

}

function showModalEventGallery() {

    $('#modal-event-award').modal('show');

}