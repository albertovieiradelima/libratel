function getUrlRegisters() {
    return url_getRegiter = "/admin/get-users";
}

function resetForm() {

    $("input[name='id']").val("");

    $('#formUser').each(function() {
        this.reset();
    });
}

$("#avatar-select").on("changed",function(){
    $("input[name='avatar']").val(iconSelect.getSelectedValue());
});

var iconSelect = new IconSelect("avatar-select", 
                {'selectedIconWidth':48,
                'selectedIconHeight':48,
                'selectedBoxPadding':1,
                'iconsWidth':48,
                'iconsHeight':48,
                'boxIconSpace':1,
                'vectoralIconNumber':5,
                'horizontalIconNumber':1});

var icons = [];
icons.push({
    'iconFilePath': '/assets/img/perfil.png',
    'iconValue': 'perfil.png'
});
icons.push({
    'iconFilePath': '/assets/img/avatar.png',
    'iconValue': 'avatar.png'
});
icons.push({
    'iconFilePath': '/assets/img/avatar2.png',
    'iconValue': 'avatar2.png'
});
icons.push({
    'iconFilePath': '/assets/img/avatar3.png',
    'iconValue': 'avatar3.png'
});
icons.push({
    'iconFilePath': '/assets/img/avatar4.png',
    'iconValue': 'avatar4.png'
});
icons.push({
    'iconFilePath': '/assets/img/avatar5.png',
    'iconValue': 'avatar5.png'
});

iconSelect.refresh(icons);

$(".sidebar-menu").find("li.active").removeClass("active");
$(".users-menu").addClass("active");
$(".users-link").addClass("active");

$('#formUser').submit(function(event) {

    event.preventDefault();

    var formData = {
        'id': $("input[name='id']").val(),
        'cpf': $("input[name='cpf']").val(),
        'name': $("input[name='name']").val(),
        'username': $("input[name='username']").val(),
        'password': $("input[name='password']").val(),
        'email': $("input[name='email']").val(),
        'avatar': $("input[name='avatar']").val(),
        'status': $("input[name='status']").is(":checked"),
        'phone': $("input[name='phone']").val(),
        'job':$("select[name='job']").select2("val"),
        'area':$("select[name='area']").select2("val")
    }

    var url;
    if ($("input[name='id']").val() != "") {
        url = "/admin/users/update";
    } else {
        url = "/admin/users/insert";
    }

    $('.modal-loading').fadeIn('slow');

    $.ajax({
        type: "POST",
        url: url,
        data: formData,
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
            $('.modal-loading').fadeOut('slow');

            var data = $.parseJSON(jqXHR.responseText);

            if (data.success == true) {
                setIdAction("");
                $('#user-modal').modal('hide');
                table.ajax.reload();
            }
        }

    });

});

$('.new').click(function(e) {
    resetForm();
});

$('.edit').click(function(e) {

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
        url: "/admin/get-users",
        data: id,
        dataType: "json",
        success: function(data) {
            if (data.success) {
                $("input[name='cpf']").val(data.user.cpf);
                $("input[name='id']").val(data.user.id);
                $("input[name='name']").val(data.user.fullname);
                $("input[name='username']").val(data.user.username);
                $("input[name='password']").val("");
                $("input[name='email']").val(data.user.email);
                $("input[name='avatar']").val(data.user.avatar);
                $("input[name='phone']").val(data.user.phone);
                $("select[name='job']").select2("val", data.user.job);
                $("select[name='area']").select2("val", data.user.area);

                var img = $('.selected-icon').find('img');
                var path;

                if(data.user.avatar === null){
                    path = '/assets/img/perfil.png';
                } else {
                    path = '/assets/img/'+data.user.avatar;
                }
                
                img.attr('src', path);

                if (data.user.status == "active") {
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
            $('.modal-loading').fadeOut('slow');
            $('#user-modal').modal('show');
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

            $('.modal-loading').fadeIn('slow');

            $.ajax({
                type: "POST",
                url: "/admin/users/delete",
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
                    $('.modal-loading').fadeOut('slow');
                    table.ajax.reload();
                }

            });
        }

    });

});
