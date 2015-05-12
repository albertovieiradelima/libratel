    function getUrlRegisters() {
        return url_getRegiter = "/admin/get-benefits";
    }

    function resetForm() {

        $("input[name='id']").val("");
        $('#fk_benefit_type option').remove();

        $('#formBenefit').each(function() {
            this.reset();
        });
    }


    $(".sidebar-menu").find("li.active").removeClass("active");
    $(".associate-menu").addClass("active");
    $(".benefits-link").addClass("active");

    $('#formBenefit').submit(function(event) {

        event.preventDefault();

        var formData = new FormData($('#formBenefit')[0]);

        var url;
        if ($("input[name='id']").val() != "") {
            url = "/admin/benefits/update";
        } else {
            url = "/admin/benefits/insert";
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
                    $('#benefit-modal').modal('hide');
                    table.ajax.reload();
                }
            }

        });

    });

    $('.new').click(function(e) {

        resetForm();

        $.ajax({
            type: "POST",
            url: "/admin/get-benefit-types",
            dataType: "json",
            success: function(data) {
                if (data.success) {
                    if(data.benefit_types.length > 0){
                        $.each(data.benefit_types, function(key, type) {
                            $('#fk_benefit_type').append('<option value="' + type.id + '">' + type.title + '</option>');
                        });
                        $('#benefit-modal').modal('show');
                    } else {
                        toastr.warning("Todos os Benefícios de Associados já foram cadastrados!");
                        $('#benefit-modal').modal('hide');
                        return;
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

            }
        });

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
            url: "/admin/get-benefits",
            data: id,
            dataType: "json",
            success: function(data) {
                if (data.success) {
                    $("input[name='id']").val(data.benefit.id);
                    var description = $('#description').data("wysihtml5").editor;
                    description.setValue(data.benefit.description);
                    $('#fk_benefit_type').append('<option value="' + data.benefit.fk_benefit_type + '">' + data.benefit.title + '</option>');
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
                $('#benefit-modal').modal('show');
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
                    url: "/admin/benefits/delete",
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
