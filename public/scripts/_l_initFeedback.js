$(document).ready(function(){
    // ********** register feedback content text length 
    $("#feedbackContent").elastic();
    $("#feedbackContent").bind("keyup input paste", function(){
        if($(this).val().length > 1000) {
            $(this).val($(this).val().substring(0, 1000));
        }
        $("#feedbackTextLeft").html(1000 - $(this).val().length);
    });
    
    // *********** register feedback submit button
    var feedbackFormValidate = $("#feedbackForm").validate({
        rules: {
            feedbackRate: {
                required: true
            }
        }
    });
    $("#simpleBoxButton").bind("click", function(){
        if(feedbackFormValidate.form()) {
            var content = $("#feedbackContent").val();
            content = content.replace("<", "&lt;");
            content = content.replace(">", "&gt;");
            content = content.replace('\n', '<br>');
            $("#feedbackContent").val(content);
            $("#feedbackForm").submit();
        }
    });
    
    // **** controller: feedback
    // *** action: view
    $("#feedbackViewSelectAll").bind("click", function(){
        $(".feedbackViewCheckbox").each(function(event, element){
            if(!($(element).is(":checked"))) {
                $(element).click();
            }
        });
    });
    $("#feedbackViewDeselectAll").bind("click", function(){
        $(".feedbackViewCheckbox").each(function(event, element){
            if($(element).is(":checked")) {
                $(element).click();
            }
        });
    });
    $("#feedbackViewDeleteAll").bind("click", function(){
        jConfirm('Are you sure want to delete all selected feedback?', 'Confirm delete', function(r) {
            if(r) {
                $(".feedbackViewCheckbox").each(function(event, element){
                    if($(element).is(":checked")) {
                        $.ajax({
                            url: "http://www.iknowu.com/iknowu/public/ajax/deletefeedback?format=html",
                            dataType: "html",
                            data: {
                                email: $(element).attr("ref1"),
                                date:  $(element).attr("ref2")
                            },
                            success: function(data){
                                if(data) {
                                    $(element).parent().remove();
                                } else {
                                    jAlert("Try again later or contact us.","Internal server error");
                                }
                            }
                        })
                    }
                });
            }
        });
        
    });
    
    // *** controller: feedback
    // *** action: viewer
    $(".feedbackViewerDate").prettyDate({
        updateInterval: 1000
    });
    
    $("#feedbackViewerMarkAsUnread").click(function(){
        $.ajax({
            url: "http://www.iknowu.com/iknowu/public/ajax/unreadfeedback?format=html",
            dataType: "html",
            data: {
                email: $("#feedbackViewerMarkAsUnread").attr("ref1"),
                date:  $("#feedbackViewerMarkAsUnread").attr("ref2")
            },
            success: function(data){
                if(data) {
                    iknowu_alert("Successfully marked as unread");
                } else {
                    jAlert("Try again later or contact us.","Internal server error");
                }
            }
        })
    });
    
    $("#feedbackViewerDelete").click(function(){
        jConfirm('Are you sure want to delete this feedback?', 'Confirm delete', function(r) {
            if(r) {
                $.ajax({
                    url: "http://www.iknowu.com/iknowu/public/ajax/deletefeedback?format=html",
                    dataType: "html",
                    data: {
                        email: $("#feedbackViewerDelete").attr("ref1"),
                        date:  $("#feedbackViewerDelete").attr("ref2")
                    },
                    success: function(data){
                        if(data) {
                            window.location = "http://www.iknowu.com/iknowu/public/feedback/view"
                        } else {
                            jAlert("Try again later or contact us.","Internal server error");
                        }
                    }
                })
            }
        });
    });
    
});