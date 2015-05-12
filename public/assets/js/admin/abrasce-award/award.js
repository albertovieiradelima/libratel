    
    /**
     * Get url registers
     * @returns {url_getRegiter|String}
     */
    function getUrlRegisters() {
        return url_getRegiter = "/admin/abrasce-award/awards/get-awards";
    }

    /**
     * Reset form
     */
    function resetForm() {
        $("input[name='id']").val("");
        $("input[name='name']").val("");
        $("input[name='description']").val("");
        $("input[name='code']").val("");
        $("input[name='inactive']").val("");
        $('#formAward').each(function() {
            this.reset();
        });
    }

    // Set menu options
    $(".sidebar-menu").find("li.active").removeClass("active");
    $(".abrasce-award-menu").addClass("active"); 
    $(".awards-link").addClass("active");

    // Submit form
    $('#formAward').submit(function(event) {

        event.preventDefault();
        
        var url;
        
        if ($("input[name='id']").val() != "") {
            url = "/admin/abrasce-award/awards/update";
        } else {
            url = "/admin/abrasce-award/awards/insert";
        }
        
        var formData = $('#formAward').serializeArray();
        
        if($("input[name='inactive']").prop('checked')){
            formData.push({name:'inactive', value:true});
        }
        
        $.post(url, formData, function(data){
           
            if (data.success) {
                setIdAction("");
                $('#award-modal').modal('hide');
                toastr.success(data.message);
                table.ajax.reload();
            }
            
        }).error(function(jqXHR,textStatus,errorThrown){
            
            if (IsJsonString(jqXHR.responseText)) {
                var data = $.parseJSON(jqXHR.responseText);
                if (data.success == false) {
                    toastr.error(data.error);
                }
            }
            
        }).always(function(data){
            
        });

    });
    
    // Button new on click
    $('.new').click(function(e) {
        resetForm();
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
        
        $.post('/admin/abrasce-award/awards/get-awards', params, function(response){
            
            if(response.success){
                
                // Populate form data
                var data = response.data;
                $("input[name='id']").val(data.id);
                $("input[name='name']").val(data.name);
                $("input[name='code']").val(data.code);
                
                // Description
                var editor = $('#description').data("wysihtml5").editor;
                editor.setValue(data.description);
                
                $("input[name='inactive']").prop('checked',(data.inactive == 1 ? true : false));
                if (data.inactive == 1) {
                    $("input[name='inactive']").iCheck('check');
                } else {
                    $("input[name='inactive']").iCheck('uncheck');
                }

            }
            
        }).error(function(jqXHR,textStatus,errorThrown){

            if (IsJsonString(jqXHR.responseText)) {
                var data = $.parseJSON(jqXHR.responseText);
                if (data.success == false) {
                    toastr.error(data.error);
                }
            }
            
        }).complete(function(data){
            $('#award-modal').modal('show');
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
                    url: "/admin/abrasce-award/awards/delete",
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
        
        window.location.href = '/admin/abrasce-award/award-field/' + _selectedID;
        
    });
