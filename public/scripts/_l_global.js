// Global.js
var autoOpenFirstTimeRegisteredDialog = false;

// ********************* Register filesize and accept validators
$.validator.addMethod('filesize', function(value, element, param) {
    return this.optional(element) || (element.files[0].size <= param);
});
$.validator.addMethod("accept", function(value, element, param) {
    // Split mime on commas incase we have multiple types we can accept
    var typeParam = typeof param === "string" ? param.replace(/,/g, '|') : "image/*",
    optionalValue = this.optional(element),
    i, file;
    // Element is optional
    if(optionalValue) {
        return optionalValue;
    }
    if($(element).attr("type") === "file") {
        // If we are using a wildcard, make it regex friendly
        typeParam = typeParam.replace("*", ".*");
        // Check if the element has a FileList before checking each file
        if(element.files && element.files.length) {
            for(i = 0; i < element.files.length; i++) {
                file = element.files[i];

                // Grab the mimtype from the loaded file, verify it matches
                if(!file.type.match(new RegExp( ".?(" + typeParam + ")$", "i"))) {
                    return false;
                }
            }
        }
    }
    // Either return true because we've validated each file, or because the
    // browser does not support element.files and the FileList feature
    return true;
}, $.format("Please enter a value with a valid mimetype."));

jQuery.validator.addMethod("nowhitespace", function(value, element) {
    return this.optional(element) || /^\S+$/i.test(value);
}, "No white space please");

jQuery.validator.addMethod("alphanumeric", function(value, element) {
    return this.optional(element) || /^\w+$/i.test(value);
}, "Letters, numbers, and underscores are allowed only.");

$(document).ready(
    function () {
        // ******* adjust width of header
        var winWidth = 900;
        $(".bodyContainer").width(winWidth);
        $(".personalStuff").width($(".bodyContainer").width()-$(".logo").width()-150);
        $(".content").width($(".bodyContainer").width());
        $('.bodyContainer').css('min-height', $(document).height());
        $('.content').css('text-align','center');
        // $('.articleContainerDiv').css('min-height', $(document).height() - $(".header").height() - $(".footer").height() - 50);

    
        // ******* prepare login form
        if($("#signinButton").length != 0){
            $("#signinForm").dialog({
                autoOpen: false,
                modal: true,
                resizable: false,
                draggable: false,
                width: 400,
                dialogClass: "signinDIV",
                title: "Welcome"
            });
            // -------- register signinLink
            $('#signinLink').click(function(){
                signinF.resetForm();
                $("#signinEmail").val("");
                $("#signinPassword").val("");
                $("#signinForm").dialog("open");
                $('#signinLog').text("");
                return false;
            });        
            
            // --------- validate signinF
            var signinF = $("#signinF").validate({
                rules: {
                    signinEmail: {
                        required: true,
                        email: true
                    },
                    signinPassword: {
                        required: true
                    }
                } 
            });
            
            // --------- register signinButton
            $('#signinButton').click(function(){
                if(!signinF.form())
                    return false;

                var email = $("#signinEmail").val();
                var password = $("#signinPassword").val();
                $.ajax({
                    type: 'POST',
                    dataType: 'json',
                    url: 'http://www.iknowu.com/iknowu/public/ajax/auth?format=json',
                    async: false,
                    data: {
                        email: email, 
                        password: password
                    },
                    success: function(json, textStatus) {
                        if(json.isLoggedin == 1){
                            $("#signinLog").css("color","chartreuse");
                            $("#signinLog").text(json.status);
                            // setTimeout(function(){
                            window.location.reload();
                        // }, 5000)
                        } else if (json.isLoggedin == 2) {
                            // incorrect password
                            $("#signinLog").css("color","#cd0a0a");
                            $("#signinLog").text(json.status);
                        } else {
                            // non-exists user
                            $("#signinLog").css("color","#cd0a0a");
                            $("#signinLog").text(json.status);
                        }
                    },
                    error: function(text, text1, text2){
                    // unable to connect to auth server
                    }
                });
                return false;
            });
        } // ########  end of prepare login form
        
        // ********* register signoutLink
        $("#signoutLink").click(function(){
            $.ajax({
                url: "http://www.iknowu.com/iknowu/public/ajax/signout?format=html",
                type: "GET",
                dataType: "html",
                async: false,
                success: function(){
                    window.location.reload();
                }
            });
            return false;
        });
    
        // *********** register profile dropdown
        $("#profileControlDropdown").tooltip({
            position: "bottom left"
        });
        var profileControlDropdownMenuWidth = $(".profileControlDropdownLi").width()+$("#headerAvatar").width();
        if (profileControlDropdownMenuWidth < 150) {
            profileControlDropdownMenuWidth = 150;
        } 
        $(".profileControlDropdownMenu").width(profileControlDropdownMenuWidth);
        
        // ************ register myCart 
        $("#myCart").click(function(){
            $("#myCartPopup").toggle();
        });

    });



