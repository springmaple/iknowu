$(document).ready(function(){
    var validateForgotPasswordSubmitForm = $("#forgotPasswordSubmitForm").validate();
    $("#forgotPasswordSubmitButton").bind("click", function(){
        if(validateForgotPasswordSubmitForm.form()){
            $("#forgotPasswordSubmitForm").submit();
        }
    });
    
    var validateForgotPasswordChangeForm = $("#forgotPasswordChangeForm").validate();
    $("#forgotPasswordChangeButton").bind("click", function(){
        if(validateForgotPasswordChangeForm.form()){
            $("#forgotPasswordChangeForm").submit();
        }
    }); 
});