$(document).ready(function(){
    // *********************
    // **** controller: reg
    // **** action: index
    // *********************

    // check if the email already exist
    var emailAvailability = false;
    $("#regEmail").bind("keyup", function(){
        emailAvailability = false;
        if($("#regEmail").valid()) {
            $("#regEmailError").html("Checking availability...");
            $("#regEmailError").css("color", "black");
        }
        delay(function(){
            if($("#regEmail").valid()) {
                $.ajax({
                    url: "http://www.iknowu.com/iknowu/public/ajax/checkemail?format=html",
                    dataType: "html",
                    data: {
                        email: $("#regEmail").val()
                    },
                    success: function(data){
                        if(data=="true") {
                            emailAvailability = true;
                            $("#regEmailError").html("Valid!");
                            $("#regEmailError").css("color", "green");
                        } else {
                            emailAvailability = false;
                            $("#regEmailError").html("This email address is already taken.");
                            $("#regEmailError").css("color", "red");
                        }
                    },
                    error: function() {
                        emailAvailability = false;
                    }
                })
            } 
        }, 200 );
    });
    
    var validateRegForm = $("#regForm").validate();
    $("#regSubmitButton").bind("click", function(){
        if(validateRegForm.form() && emailAvailability == true){
            $("#regForm").submit();
        }
    });
    
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