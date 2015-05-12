function getUrlRegisters() {
    return url_getRegiter = "/admin/restricted-area/get-filecategory";
}

function resetForm() {

    $("input[name='id']").val("");

    $('#formFileCategory').each(function() {
        this.reset();
    });
}


$(".sidebar-menu").find("li.active").removeClass("active");
$(".file-menu").addClass("active");
$(".filecategories-link").addClass("active");

var updateOutput = function(e)
{
    var list   = e.length ? e : $(e.target),
        output = list.data('output');
    if (window.JSON) {
        output.val(window.JSON.stringify(list.nestable('serialize')));//, null, 2));
    } else {
        output.val('JSON browser support required for this demo.');
    }
};

var obj = '[{"id":1},{"id":2},{"id":4},{"id":6},{"id":5},{"id":7},{"id":8},{"id":9},{"id":11},{"id":10},{"id":12},{"id":3}]';
var output = '';
function buildItem(item) {

    var html = "<li class='dd-item' data-id='" + item.id + "'>";
    html += "<div class='dd-handle'>" + item.id + "</div>";

    if (item.children) {

        html += "<ol class='dd-list'>";
        $.each(item.children, function (index, sub) {
            html += buildItem(sub);
        });
        html += "</ol>";

    }

    html += "</li>";

    return html;
}

$.each(JSON.parse(obj), function (index, item) {

    output += buildItem(item);

});

$('.dd-list').html(output);
$('#nestable').nestable()
    .on('change', updateOutput);
console.log(updateOutput($('#nestable').data('output', $('#nestable-output'))));
//updateOutput($('#nestable').data('output', $('#nestable-output')));

$('#formFileCategory').submit(function(event) {

    event.preventDefault();

    var formData = new FormData($('#formFileCategory')[0]);

    var url;
    if ($("input[name='id']").val() != "") {
        url = "/admin/restricted-area/filecategory/update";
    } else {
        url = "/admin/restricted-area/filecategory/insert";
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
                $('#filecategory-modal').modal('hide');
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
        url: "/admin/restricted-area/get-filecategory",
        data: id,
        dataType: "json",
        success: function(data) {
            if (data.success) {
                $("input[name='id']").val(data.filecategory.id);
                $("input[name='name']").val(data.filecategory.name);

                if (data.filecategory.status == 1) {
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
            $('#filecategory-modal').modal('show');
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
                url: "/admin/restricted-area/filecategory/delete",
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
