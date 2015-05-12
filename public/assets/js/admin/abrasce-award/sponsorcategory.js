/**
 * Created by albertovieiradelima on 27/01/15.
 */
function getUrlRegisters() {
    return url_getRegiter = "/admin/abrasce-award/get-sponsorcategory";
}

function resetForm() {

    $("input[name='id']").val("");

    $('#formSponsorCategory').each(function() {
        this.reset();
    });
}

$(".sidebar-menu").find("li.active").removeClass("active");
$(".abrasce-award-menu").addClass("active");
$(".award-sponsors-submenu").addClass("active");
$(".award-sponsorcategories-link").addClass("active");

$('#formSponsorCategory').submit(function(event) {

    event.preventDefault();

    var formData = new FormData($('#formSponsorCategory')[0]);

    var url;
    if ($("input[name='id']").val() != "") {
        url = "/admin/abrasce-award/sponsorcategory/update";
    } else {
        url = "/admin/abrasce-award/sponsorcategory/insert";
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
                $('#sponsorcategory-modal').modal('hide');
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
        url: "/admin/abrasce-award/get-sponsorcategory",
        data: id,
        dataType: "json",
        success: function(data) {
            if (data.success) {
                $("input[name='id']").val(data.sponsorcategory.id);
                $("input[name='name']").val(data.sponsorcategory.name);

                if (data.sponsorcategory.status == "active") {
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
            $('#sponsorcategory-modal').modal('show');
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
                url: "/admin/abrasce-award/sponsorcategory/delete",
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
