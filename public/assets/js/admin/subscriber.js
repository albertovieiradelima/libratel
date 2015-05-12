    function getUrlRegisters() {
        return url_getRegiter = "/admin/get-subscribers";
    }

    function resetForm() {

        $("input[name='id']").val("");

        $('#formSubscriber').each(function() {
            this.reset();
        });
    }

    $(".sidebar-menu").find("li.active").removeClass("active");
    $(".courses_events-menu").addClass("active");
    $(".subscribers-link").addClass("active");

    $.ajax({
        type: "POST",
        url: '/admin/get-subscriber-events',
        cache: false,
        contentType: false,
        processData: false,
        dataType: "json",
        success: function(data) {
            var results = [];
            $.each(data, function(index, item) {
                results.push({
                    id: item.id,
                    text: item.title
                });
            });
            $('#fk_event').select2({
                data: results
            });
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

        }

    });

    $('#formSubscriber').submit(function(event) {

        event.preventDefault();

        var formData = new FormData($('#formSubscriber')[0]);

        var url;
        if ($("input[name='id']").val() != "") {
            url = "/admin/subscriber/update";
        } else {
            url = "/admin/subscriber/insert";
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
                    $('#subscriber-modal').modal('hide');
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
            url: "/admin/get-subscribers",
            data: id,
            dataType: "json",
            success: function(data) {
                if (data.success) {
                    $("input[name='id']").val(data.subscriber.id);
                    $("input[name='name']").val(data.subscriber.name);
                    $("input[name='email']").val(data.subscriber.email);
                    $("input[name='cpf']").val(data.subscriber.cpf);
                    $("#job").val(data.subscriber.job);
                    $("input[name='business']").val(data.subscriber.business);
                    $("input[name='phone']").val(data.subscriber.phone);
                    $("input[name='date']").val(data.subscriber.date);
                    $("#fk_event").select2('val', data.subscriber.fk_event);

                    if (data.subscriber.status == "registered") {
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
                $('#subscriber-modal').modal('show');
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
                    url: "/admin/subscriber/delete",
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
