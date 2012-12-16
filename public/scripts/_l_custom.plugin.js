/*
 * custom plugin for jquery
 */

/*
 *  Method signature:
 *      iknowu_alert(content[, time = 5000 [, fadeTime = 200]])
 */
function iknowu_alert(content, time, fade_time){
    if($(".iknowu_alert").length != 0){
        $(".iknowu_alert").remove();
    }
    if(time == null){
        time = 5000;
    }
    if(fade_time == null){
        fade_time = 3000;
    }
    var d = new Date();
    var randomId = d.getMinutes().toString() + d.getSeconds().toString() +  d.getMilliseconds().toString();
    $("body").append("<span id='"+randomId+"' class='iknowu_alert'>"+ content +"</span>");
    
    // $("#"+randomId).css("top", Math.max(0, (($(window).height() - $(".iknowu_alert").outerHeight()) / 2)) + 
    //    $(window).scrollTop()) + "px");
    $("#"+randomId).css("left", Math.max(0, (($(window).width() - $(".iknowu_alert").outerWidth()) / 2) + 
        $(window).scrollLeft()) + "px");
    $("#"+randomId).bind("click", function(){
        $(this).remove();
    });
    
    setTimeout(function(){
        if(!$("#"+randomId).is(":hover")){
            $("#"+randomId).fadeOut(fade_time, function(){
                $("#"+randomId).remove();
            });
        }
    }, time);
    $("#"+randomId).hover(function() {
        $("#"+randomId).stop(true, false);
        $("#"+randomId).css("opacity","1");
    }, function() {
        $("#"+randomId).stop(true, false).fadeOut(fade_time, function(){
            $("#"+randomId).remove();
        });
    });
}