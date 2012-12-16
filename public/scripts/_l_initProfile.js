$(document).ready(function(){
    $("#profileEditAvatarImg").tooltip({
        position: "bottom center"
    });
    
    // ---------- register photo upload form and validator
    $("#profileEditAvatarImg").click(function(){
        $("#profileEditAvatarUploadInput").click();
    });
    
    var validateAvatar = $('#profileEditForm').validate({
        rules: {
            profileEditAvatarUploadInput: {
                required: true,
                accept: "png|jpe?g|gif",
                filesize: 1048576
            }
        },
        messages: {
            profileEditAvatarUploadInput: "File must be in PNG, JPG or GIF format and less than 1MB"
        }
    });
    $("#profileEditForm").ajaxForm({
        dataType: 'json',
        success: function(status){
            if(status.success){
                iknowu_alert(status.status);
                $("#profileEditAvatarImg").attr("src", "/iknowu/public/images/avatars/" + status.newName);
                $("#headerAvatar").attr("src", "/iknowu/public/images/avatars/" + status.newName);
            } else {
                iknowu_alert("Internal server error, please try again later.");
            }
            return false;
        },
        error: function(error, err, er){
            iknowu_alert(er);
            return false;
        },
        uploadProgress: function(event, position, total, percentCompleted){
            iknowu_alert("Image uploaded " + percentCompleted + "%");
            return false;
        }
    });
    $("#profileEditAvatarUploadInput").change(function(){
        if(validateAvatar.form()){
            $("#profileEditForm").submit();
        }
    });

    
    // ****** create custom radio button for gender in EDIT
    $( "#genderRadioSet" ).buttonset();
    
    // ****** register change password button
    $("#profileEditChangePassword").click(function(){
        window.location = "http://localhost/iknowu/public/profile/editpassword"; 
    });

    // ******* register profile deactivating actions
    $("#profileEditDeactivateDiv").hide();
    $("#profileEditDeactivateButton").click(function(){
        $("#profileEditDeactivateButton").hide();
        $(".profileEditSettingBox").hide();
        $("#profileEditSaveButton").hide();
        $("#profileEditDeactivateDiv").slideDown(0, function(){
            $("#profileEditDeactivateDiv").expose({
                onClose: function(){
                    $(".profileEditSettingBox").show();
                    $("#profileEditSaveButton").show();
                    $("#profileEditDeactivateDiv").hide();
                    $("#profileEditDeactivateButton").show();
                }
            });
        });
        return false;
    });
    $("#profileEditDeactivateButtonCancel").click(function() {
        $.mask.close();
        return false; 
    });
    
    // ********** register question icon
    $(".profileEditQuestionMark").tooltip({
        position: "bottom center"
    });
    
    // ********** register profileEditSaveButton 
    $("#profileEditSaveButton").click(function(){
        var nickname = $("[name='nickname']").val();
        var gender =  $("[name='gender']:checked").val();
        var name =  $("[name='name']").val();
        var address = $("[name='address']").val();
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: 'http://localhost/iknowu/public/ajax/profileedit?format=json',
            async: false,
            timeout: 15000,
            data: {
                nickname: nickname,
                gender: gender,
                name: name,
                address: address
            },
            success: function(json, textStatus) {
                iknowu_alert("Changes are Saved");
            },
            beforeSend: function(){
                iknowu_alert("Submitting Your Changes ...", 15000);
            },
            error: function(text,text1,text2){
                iknowu_alert("Error on submitting form: " + text2);
            }
        });
    });
    
    // ************** register first time login dialog
    $("#firstTimeRegistered").dialog({
        autoOpen: autoOpenFirstTimeRegisteredDialog,
        modal: true,
        resizable: false,
        draggable: false,
        width: 400,
        dialogClass: "signinDIV"
    });
    
    $("#firstTimeRegisteredYes").click(function(){
        $("#firstTimeRegistered").dialog('close');
    });
});
