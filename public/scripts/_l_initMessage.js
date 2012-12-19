$(document).ready(function(){
    $(".updateIndexFilterLi").bind("click", function() {
        var id=$(this).attr("uid");
        $(".updateIndexFilterLi").each(function(index, element){
            $(element).css("background-color", "white");
        });
        $(this).css("background-color", "grey");
        
        $("#updateIndexIframe").attr("src", "http://www.iknowu.com/iknowu/public/update/updateframe?id=" + id);
    });
});
