function Facebook_login() {
    $("#signinWaitImg").css("display","inline-block");    
    $("#signinWaitImg").show();
    FB.login(function(response) {
        // var accessToken;
        if (response.status === 'connected') {
            //var uid = response.authResponse.userID;
            //var accessToken = response.authResponse.accessToken;
            // alert("connected");
            // accessToken = response.authResponse.accessToken;
            $.ajax({
                url: "http://www.iknowu.com/iknowu/public/ajax/fbauth?format=html",
                type: "GET",
                dataType: "html",
                async: false,
                data: {
                   // accessToken: accessToken
                },
                success: function(){
                    $("#signinWaitImg").hide();
                    window.location.reload();
                },
                beforeSend: function(){
                    $("#signinWaitImg").show();
                },
                error: function(x, y, z){
                    alert(z);
                }
            });
        } else if (response.status === 'not_authorized') {
            alert("not_authorized");
            $("#signinWaitImg").hide();
        } else {
            alert("Not_login");
            $("#signinWaitImg").hide();
        }
    }, {scope: 'email'});
    return false;
}
