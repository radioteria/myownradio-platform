(function(){
    
    if($("#modifyProfile").children().find("input").filter(".error").length > 0)
    {
        alert("Not validated");
    }
    
    $("#modifyProfile").live('submit', function(event){
        $.post("", $(this).serialize(), function(json){
            try
            {
                if(json.code === "UPDATED")
                {
                    console.log("Information updated!");
                    showPopup("Profile updated")
                }
                else if(json.code === "PERMALINK_USED")
                {
                    $("#permalink").addClass("error");
                    $(".rm-profile-wrapper-permalink-validate").text("This permalink already in use. Please try another.").show();
                }
                else if(json.code === "PERMALINK_LENGTH_UNACCEPTABLE")
                {
                    $("#permalink").addClass("error");
                    $(".rm-profile-wrapper-permalink-validate").text("Permalink must be between 3 to 255 characters.").show();
                }
                else
                {
                    showPopup("No changes to profile were made");
                }
            }
            catch(e){}
        });
        return false;
    });
    
    var tmr = false;
    
    $("#permalink").livequery('textchange', function(event){
        if(tmr)
        {
            window.clearTimeout(tmr);
        }
        
        tmr = window.setTimeout(function(){
            tmr = false;
            $.post("", {
                permalink: $("#permalink").val(),
                action: "checkPermalink"
            }, function(json){
                try
                {
                    if(json.code === "PERMALINK_FREE")
                    {
                        $("#permalink").removeClass("error");
                        $(".rm-profile-wrapper-permalink-validate").hide().text("");
                    }
                    else if(json.code === "PERMALINK_USED")
                    {
                        $("#permalink").addClass("error");
                        $(".rm-profile-wrapper-permalink-validate").text("This permalink is unavailable. Please try another.").show();
                    }
                    else if(json.code === "PERMALINK_LENGTH_UNACCEPTABLE")
                    {
                        $("#permalink").addClass("error");
                        $(".rm-profile-wrapper-permalink-validate").text("Permalink must be between 3 to 255 characters").show();
                    }
                    else if(json.code === "PERMALINK_WRONG_CHARS")
                    {
                        $("#permalink").addClass("error");
                        $(".rm-profile-wrapper-permalink-validate").text("Permalink must contain only a-z, 0-9 and \"-\" characters").show();
                    }
                }
                catch(e){}
            });
        }, 250);

    });
    
    // Upload cover
    $(".rmButton#changeImage").live("click", function(event){
        $("input[type='file']").click();
    });
    
    $("input[type='file']").livequery(function(){
        $(this).on('change', function(event)
        {
 
            var data = new FormData();
            
            data.append('file', event.target.files[0]);
            data.append('authtoken', mor.user_token);
            data.append('action', 'avatar');
    
            uploadHandle = $.ajax({
                type: "POST",
                url: "",
                data: data,
                processData: false,
                contentType: false,
                cache: false,
                success: function(data) {
                    try
                    {
                        var json = JSON.parse(data);
                        if(json.code === "SUCCESS")
                        {
                            $(".rm-profile-wrapper-cover-image > img").appendTo($(".rm-profile-wrapper-cover-image"));
                            var newImage = $(".rm-profile-wrapper-cover-image > img").attr("src").replace(/(&rnd\=([\d.]+))/g, '') + "&rnd=" + Math.random();
                            $(".rm-profile-wrapper-cover-image > img").attr("src", newImage);
                            showPopup("Image updated successfully");
                        }
                    }
                    catch(e) { }
                },
                error: function() {
                    console.error("Upload error!");
                }
            });
            
        });
    });

    // Tools
    $("#changePwd").live("click", function() {
        callModuleFunction("dialogs.changePassword");
        return false;
    });
    
})();