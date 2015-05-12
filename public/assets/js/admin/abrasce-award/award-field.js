    
    //////////////////////////////
    //                          //
    //       Init methods       //
    //                          //
    //////////////////////////////
    
    /**
     * Get url registers
     * @returns {url_getRegiter|String}
     */
    function getUrlRegisters() {
        return url_getRegiter = "/admin/abrasce-award/award-field/"+_awardID+"/get-data";
    }

    /**
     * Reset form
     */
    function resetForm() {

        // Remove has-error classes
        $("#formAwardField div").removeClass('has-error');
        $("input[name='id']").val("");
        $("select[name='type']").val("");
        $("input[name='title']").val("");
        $("input[name='weight']").val("");
        $("input[name='order']").val("");
        $("input[name='accept_filetypes']").val("");
        $('#formAwardField').each(function() {
            this.reset();
        });
    }

    // Set menu options
    $(".sidebar-menu").find("li.active").removeClass("active");
    $(".abrasce-award-menu").addClass("active"); 
    $(".awards-link").addClass("active");
    
    // Set selection option
    $("#select-award").val(_awardID);
    $("#select-award").change(function(e){
        _loader.show();
        window.location.href = "/admin/abrasce-award/award-field/" + $(this).val();
    });
    
    $("select[name=type]").change(function(e){
        if($(this).val() == 'file'){
            console.log('Show');
            $('.accept-filetypes').show();
            $('.accept-text').hide();
        }else{
            console.log('Hide');
            $('.accept-filetypes').hide();
            $('.accept-text').show();
        }
    });
    
    //////////////////////////////
    //                          //
    //       Form methods       //
    //                          //
    //////////////////////////////
    
    /**
     * Submit form
     */
    // Define form validation
    var _form = $('.form-validate').validate({

        submitHandler: function(form) {

            var url;
            if ($("input[name='id']").val() != "") {
                url = "/admin/abrasce-award/award-field/update";
            } else {
                url = "/admin/abrasce-award/award-field/insert";
            }

            var formData = $('#formAwardField').serializeArray();

            $.post(url, formData, function(data){

                if (data.success) {
                    setIdAction("");
                    $('#award-field-modal').modal('hide');
                    toastr.success(data.message);
                    table.ajax.reload();
                }else{
                    toastr.error(data.message);
                }

            }).error(function(jqXHR,textStatus,errorThrown){

                if (IsJsonString(jqXHR.responseText)) {
                    var data = $.parseJSON(jqXHR.responseText);
                    if (data.success == false) {
                        toastr.error(data.error);
                    }
                }

            }).always(function(data){
                // always
            });
        }
    });
    
    //////////////////////////////
    //                          //
    //         Buttons          //
    //                          //
    //////////////////////////////
    
    // Button new on click
    $('.new').click(function(e) {
        _form.resetForm();
        resetForm();
        $("select[name=type]").val('');
        $('.accept-filetypes').hide();
        $('.accept-text').show();
        $("input[name='fk_award']").val(_awardID);
    });

    // Button edit on click
    $('.edit').click(function(e) {

        resetForm();

        if (!$(this).attr("id")) {
            toastr.error("Selecione um registro!");
            return;
        }
        
        var params = {
            'id': $(this).attr("id")
        };
        
        $.post('/admin/abrasce-award/award-field/'+_awardID+'/get-data', params, function(response){
            
            if(response.success){
                
                // Populate form data
                var data = response.data;
                $("input[name='id']").val(data.id);
                $("select[name='type']").val(data.type);
                $("input[name='title']").val(data.title);
                $("input[name='weight']").val(data.weight);
                $("input[name='order']").val(data.order);
                $("input[name='maxlength']").val(data.maxlength);
                $("input[name='accept_filetypes']").val(data.accept_filetypes);
                $("input[name='fk_award']").val(data.fk_award);
                
                if(data.type == 'file'){
                    $('.accept-filetypes').show();
                    $('.accept-text').hide();
                }else{
                    $('.accept-filetypes').hide();
                    $('.accept-text').show();
                }
                
                // Description
                var editor = $('#description').data("wysihtml5").editor;
                editor.setValue(data.description);
                
            }
            
        }).error(function(jqXHR,textStatus,errorThrown){

            if (IsJsonString(jqXHR.responseText)) {
                var data = $.parseJSON(jqXHR.responseText);
                if (data.success == false) {
                    toastr.error(data.error);
                }
            }
            
        }).complete(function(data){
            $('#award-field-modal').modal('show');
        });

    });

    // Button remove on click
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
                    url: "/admin/abrasce-award/award-field/delete",
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
    
    // Edit fields on click
    $('.edit-fields').click(function(e){
        
        if (!_selectedID) {
            toastr.error("Selecione um registro!");
            return;
        }
        
        $('#award-modal-fields').modal('show');
        
    });