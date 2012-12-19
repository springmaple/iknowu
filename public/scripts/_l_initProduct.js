/*
 * controller: product
 * action: index
 */
var productIndexMagnifyHref;
$(document).ready(function(){
    // *********** register close product 
    $("#productIndexCloseProduct").click(function(){
        var id = $(this).attr("ref1");
        $.ajax({
            url: "http://www.iknowu.com/iknowu/public/ajax/closeproduct?format=html",
            dataType: "html",
            data: {
                id: id
            },
            success: function(data) {
                if(data) {
                    iknowu_alert("Successfully removed this product.");
                    setTimeout(function(){
                        window.location = "http://www.iknowu.com/iknowu/public/profile";
                    }, 5000)
                } else {
                    jAlert("Error", "Unable to remove the particular product, please try again later.");
                }
            }
        });
    });
    
    // ********** register rating
    $(".rateit").bind("rated", function(event, value) {
        pid = $("#productId").val();
        $.ajax({
            url: "/iknowu/public/ajax/rate?format=html", 
            data: {
                rate: value,
                pid: pid
            },
            dataType: "html",
            success: function(response){
                if(response) {
                    iknowu_alert("Rate submitted successfully");
                } else {
                    jAlert("Please sign in to rate.", "Not signed in");
                    $(".rateit").rateit("value", 0);
                }
            },
            error: function(er,err, errr) {
                jAlert("There is something wrong with your connection...", "Connection error");
            }
        });
    });
    
    // ********** register product index write comment text area 
    $("#productIndexWriteComment").elastic();
    $("#productIndexWriteComment").bind("keyup input paste", function(){
        if($(this).val().length > 250) {
            $(this).val($(this).val().substring(0, 250));
        }
        $("#productIndexCommentCharRemaining").html(250 - $(this).val().length);
    });
    $("#productIndexWriteComment").blur(function(){
        if($.trim($(this).val())==""){
            $(this).val("Write your comment here...");
            $(this).addClass("productIndexWriteCommentTextAreaBlur");
        }
    });
    $("#productIndexWriteComment").focus(function(){
        if($(this).hasClass("productIndexWriteCommentTextAreaBlur")){
            $(this).removeClass("productIndexWriteCommentTextAreaBlur");
            $(this).val("");
        }
    });
    
    // ************ register pretty date (jquery plugin) for the comment date
    $(".productIndexCommentDate").prettyDate({
        interval: 60000
    });
    
    // ************ register write comment submit button
    $("#productIndexWriteCommentSubmitButton").click(function(){
        if($("#productIndexWriteComment").hasClass("productIndexWriteCommentTextAreaBlur")) {
            jAlert("Please write some comment before you submit.", "Empty comment");
        } else {
            var pid = $("#productIndexPid").val();
            var comment = $("#productIndexWriteComment").val();
            comment = comment.replace("<", "&lt;");
            comment = comment.replace(">", "&gt;");
            comment = comment.replace('\n', '>');
            $.ajax({
                type: "GET",
                url: "/iknowu/public/ajax/uploadcomment?format=html",
                dataType: "html",
                data: {
                    pid: pid,
                    comment: comment
                },
                success: function(data) {
                    location.reload();
                },
                error: function() {
                    jAlert("Unable to make request to the server now, please try again later.", "Internal Server Error");
                }
            })
        }
    });
    
    // *********** register delete commment <a>x</a>
    $(".productIndexCommentDeleteA").hide();
    $(".productIndexCommentLi").hover(function(){
        $(this).find(".productIndexCommentDeleteA").show();
    }, function(){
        $(this).find(".productIndexCommentDeleteA").hide();
    });
    $(".productIndexCommentDeleteA").click(function(){
        jConfirm("Are you sure want to delete this comment?", "Delete comment", function(value){
            if(value) {
                var pid = $(".productIndexCommentDeleteA").attr('pid');
                var date = $(".productIndexCommentDeleteA").attr('date');
                $.ajax({
                    type: "GET",
                    url: "/iknowu/public/ajax/deletecomment?format=html",
                    dataType: "html",
                    data: {
                        pid: pid,
                        date: date
                    },
                    success: function(data) {
                        location.reload();
                    },
                    error: function() {
                        jAlert("Unable to make request to the server now, please try again later.", "Internal Server Error");
                    }
                })
            }
        })
    });
    
    // ********** register magnifier facebox
    $("#productIndexMagnifyImg").click(function(){
        TINY.box.show({
            image: productIndexMagnifyHref,
            width: 500,
            height: 500,
            boxid: "productIndexMagnifyContentImg"
        });
    });
    
});


/*
 * controller: product
 * action: upload
 */

$(document).ready(function(){
    // ********* register product description textarea elastic
    $("#productUploadDesc").elastic();
    // ********* register productUploadAddSize button to dropdown sizes
    $("#productUploadSizeUl").hide();

    $("#productUploadAddSize").click(function(){
        $("#productUploadSizeUl").toggle();
    });
    
    $( "#productUploadSizeUlToAdd" ).sortable();
    $( "#productUploadSizeUlToAdd" ).disableSelection();
    $(".productUploadSizesList").click(function(){
        var text = $(this).text();
        text = $.trim(text);
        $("#productUploadSizeUlToAdd").append(
            "<li>" +
            "<div class='productUploadSizeDiv'> <label class='productUploadSizeLabel'>" + text + "</label>" +
            "<input name='" + text + "' class='profileUploadSpinner' value='0'>" +
            "</div>" +
            "</li>"
            );

        // ********** register spinner for profile upload
        var intVal;
        $(".profileUploadSpinner").spinner({
            max: 999,
            min: 0,
            culture: 'n',
            spin: function(e, ui){
                if(ui.value < 0) {
                    ui.value = 0;
                } else if (ui.value > 999) {
                    ui.value = 999;
                }
            },
            change: function(e, ui) {
                intVal = parseInt($(this).val()) || 0;
                if(intVal < 0) {
                    intVal = 0;
                } else if (intVal > 999) {
                    intVal = 999;
                }
                $(this).val(intVal);
            }
        }); 
        if($("#productUploadSizeUl li").length == 1) {
            $("#productUploadAddSize").remove();
        }
        $(this).remove();
    });
    
    // ********** register select dropdown to remove 1st option on change
    $("#productUploadMainCategorySelect").change(function(element){
        if($("#productUploadMainCategorySelect option:first-child").val() == ""){
            $("#productUploadMainCategorySelect option:first-child").remove();
        }
        $("#productUploadSubCategorySelect").hide();
        $("#productUploadSubCategorySelect").html('');
        $("#productUploadSubSubCategorySelect").hide();
        $("#productUploadSubSubCategorySelect").html('');
        $.ajax({
            type: "GET",
            url: "/iknowu/public/ajax/getcategory?format=json",
            dataType: "json",
            data: {
                mode: "sub", 
                value: $("#productUploadMainCategorySelect option:selected").val()
            },
            success: function(data) {
                $("#productUploadSubCategorySelect").html("");
                $("#productUploadSubCategorySelect").append(generateOptions("", "selected"));
                if(data.data.length > 1){
                    $.each(data.data, function(index, value){
                        $("#productUploadSubCategorySelect").append(generateOptions(value));
                    })
                    $("#productUploadSubCategorySelect").show();
                } else {
                    $("#productUploadSubCategorySelect").html('');
                    $("#productUploadSubCategorySelect").hide();
                }
            }
        })
    });
    $("#productUploadSubCategorySelect").change(function(element){
        if($("#productUploadSubCategorySelect option:first-child").val() == ""){
            $("#productUploadSubCategorySelect option:first-child").remove();
        }
        $("#productUploadSubSubCategorySelect").hide();
        $("#productUploadSubSubCategorySelect").html('');
        $.ajax({
            type: "GET",
            url: "/iknowu/public/ajax/getcategory?format=json",
            dataType: "json",
            data: {
                mode: "subsub", 
                value: $("#productUploadMainCategorySelect option:selected").val(),
                value1: $("#productUploadSubCategorySelect option:selected").val()
            },
            success: function(data) {
                $("#productUploadSubSubCategorySelect").html("");
                $("#productUploadSubSubCategorySelect").append(generateOptions("", "selected"));
                if(data.data.length > 1){
                    $.each(data.data, function(index, value){
                        $("#productUploadSubSubCategorySelect").append(generateOptions(value));
                    })
                    $("#productUploadSubSubCategorySelect").show();
                } else {
                    $("#productUploadSubSubCategorySelect").html('');
                    $("#productUploadSubSubCategorySelect").hide();
                }
            }
        })
    });
    $("#productUploadSubSubCategorySelect").change(function(element){
        if($("#productUploadSubSubCategorySelect option:first-child").val() == ""){
            $("#productUploadSubSubCategorySelect option:first-child").remove();
        }
    });
    $("#productUploadGenderSelect").change(function(element){
        if($("#productUploadGenderSelect option:first-child").val() == ""){
            $("#productUploadGenderSelect option:first-child").remove();
        }
    });
    $("#productUploadSubCategorySelect").hide();
    $("#productUploadSubSubCategorySelect").hide();
    
    // ************* register autocomplete 
    $( "#productUploadBrand" ).autocomplete({
        source: "/iknowu/public/ajax/getbrand?format=html",
        delay: 200
    });
    $(".ui-corner-all").css({
        fontSize:"0.8em"
    });
    
    // ************* register to format price
    // convert price to correct format first
    $("#productUploadPrice").bind('change',function () { 
        $(this).val(function(i, v) {
            return parseFloat(v).toFixed(2);
        });
    }); 
    // ************* register productUploadNextButton 
    $("#productUploadNextButton").click(function(){
        var pass = true;
            
        // validate name, price, and gender
        pass = productFormValidate.form();
            
        // validate sizes
        var sizeValue, sizePass = false;
        $(".profileUploadSpinner").each(function(index, element){
            sizeValue = $(element).val();
            sizeValue = parseInt(sizeValue);
            if(sizeValue > 0) {
                sizePass = true;
            }
        });
        if(sizePass == false) {
            pass = false;
            $("#productUploadAddSizeError").text("You must add at least one item to any size for sell.");
            $("#productUploadAddSizeError").show();
        } else {
            $("#productUploadAddSizeError").text("");
        }
        
        // validate category
        if($("#productUploadMainCategorySelect").css("display") != "none" && $("#productUploadMainCategorySelect").find(":selected").val() == "") {
            pass = false;
            $("#productUploadCategoryError").html("This field selection is incomplete.");
        } else if ($("#productUploadSubCategorySelect").css("display") != "none" && $("#productUploadSubCategorySelect").find(":selected").val() == "") {
            pass = false;
            $("#productUploadCategoryError").html("This field selection is incomplete.");
        } else if ($("#productUploadSubSubCategorySelect").css("display") != "none" && $("#productUploadSubSubCategorySelect").find(":selected").val() == "") {
            pass = false;
            $("#productUploadCategoryError").html("This field selection is incomplete.");
        } else {
            $("#productUploadCategoryError").html("");
        }
            
            
        if(pass) {
            $("#productUploadForm").submit();
        } else {
            jAlert("The form is not yet filled in correctly. Please check again.", "Form incomplete");
        }
    });

    // ************** register productUploadForm validations
    var productFormValidate = $("#productUploadForm").validate({
        rules: {
            productUploadName: {
                required: true,
                maxlength: 100
            },
            productUploadPrice: {
                required: true,
                number: true,
                range: [0.1, 9999]
            },
            productUploadGenderSelect: {
                required: true
            },
            productUploadDesc: {
                maxlength: 500
            }
        }
    });
    
    // *************** register colorPickImg
    $("#colorPickInput").spectrum({
        color: "",
        showPaletteOnly: true,
        palette: [
        ['aqua', 'black', 'blue', 'fuchsia'],
        ['gray', 'green', 'lime', 'maroon'],
        ['navy', 'olive', 'purple', 'red'],
        ['silver', 'teal', 'white', 'yellow']
        ],
        showButtons: false,
        preferredFormat: "name",
        showInput: true,
        clickoutFiresChange: true,
        change: function(color) {
            var insertPass = true;
            if($(".productUploadColor").length == 8) {
                jAlert("You can only choose up to 8 colors.", "Maximum number of colors reached");
                return false;
            }
            $(".productUploadColor").each(function(){
                if($(this).val() == color.toHexString(true)){
                    jAlert("The color is already chosen.", "Color selection repeated");
                    insertPass = false;
                    return false;
                }
            });
            if(insertPass) {
                $("#colorPickInput").before(
                    "<span class='spanMouse' style='display: inline-block'>" + 
                    "<input class='productUploadColor' name='productUploadColor[]' type='color' value='"+color.toHexString(true)+"' readonly='readonly' />" +
                    "</span>"
                    );
                $(".spanMouse").click(function(){
                    $(this).remove();
                });
            }
        }
    });
});

function removeElementOnRightClick(e) {
    alert("123");
    if(e.button == 2){
        $(this).remove();
    }
}
function generateOptions(value){
    return "<option value='"+ value +"'>" + value + "</option>";
}

/*
 * controller: product
 * action: confirmupload
 */

$(document).ready(function(){
    // ****** register image slides
    productIndexMagnifyHref = $("#productIndexMagnifyImg").attr("href");
    $("#slides").slides({
        bigTarget: true,
        generatePagination: false,
        animationComplete: function(current){
            var element = $(".productIndexSlidesImg").get(current-1);
            productIndexMagnifyHref = $(element).attr("src");
        }
    });
    
    // ***** register price img tooltip
    $("#productIndexPriceImg").tooltip({
        position: "bottom right"
    });
    
    // ******* register confirmUploadButton
    $("#confirmUploadButton").click(function(){
        if($("#agree").is(":checked")) {
            $("#confirmUploadForm").submit();
        } else {
            jAlert("You have to agree with the seller consent first.", "Wait");
        }
    });
});