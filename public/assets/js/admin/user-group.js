/**
 * Created by albertovieira on 5/5/15.
 */
function getUrlRegisters() {
    return url_getRegiter = "/admin/get-user-group";
}

function resetForm() {

    $("input[name='id']").val("");
    $("#fk_file_category").select2('val', '');

    $('#formUserGroup').each(function() {
        this.reset();
    });
}

$(".sidebar-menu").find("li.active").removeClass("active");
$(".users-menu").addClass("active");
$(".user-group-link").addClass("active");

function getFileCategories() {

    $.ajax({
        type: "POST",
        url: "/admin/get-user-group-file-category",
        dataType: "json",
        success: function(data) {
            if (data.length > 0) {
                $.each(data, function(key, fileCategory) {
                    $('#fk_file_category').append('<option value="' + fileCategory['id'] + '">' + fileCategory['name'] + '</option>');
                });
            }
        },
        error: function(jqXHR, ajaxOptions, thrownError) {
            if (IsJsonString(jqXHR.responseText)) {
                var data = $.parseJSON(jqXHR.responseText);

                if (data.success == false) {
                    toastr.error(data.message);
                }
            }
        },
        complete: function() {

        }
    });
}

$('#formUserGroup').submit(function(event) {

    event.preventDefault();

    var formData = new FormData($('#formUserGroup')[0]);

    var url;
    if ($("input[name='id']").val() != "") {
        url = "/admin/user-group/update";
    } else {
        url = "/admin/user-group/insert";
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
                $('#user-group-modal').modal('hide');
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
        url: "/admin/get-user-group",
        data: id,
        dataType: "json",
        success: function(data) {
            if (data.success) {
                $("input[name='id']").val(data.user_group.id);
                $("input[name='name']").val(data.user_group.name);

                var selectedValues = [];
                $.each(data.user_group_file_category, function(key, file_category) {
                    selectedValues.push(file_category['fk_file_category'])
                });
                $("#fk_file_category").select2('val', selectedValues);

                if (data.user_group.status == "1") {
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
            $('#user-group-modal').modal('show');
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
                url: "/admin/user-group/delete",
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
