var popHandle = false;

function showPopup(msg)
{
    $(".rm-popup-window").stop().text(msg).addClass("visible");
    if(popHandle) {
        window.clearTimeout(popHandle);
    }
    popHandle = window.setTimeout(function() {
        popHandle = false;
        $(".rm-popup-window").removeClass("visible");
    }, 5000);
}

function myOwnQuestion(question, action) {
    question = question || "Are you sure?";
    action = action || function () {};
    var dialog = $("#myOwnQuestionTemplate").tmpl({message:question});
    dialog.find("._close-question").on("click", function () { 
        dialog.remove();
    });
    dialog.find("._accept-question").on("click", function () { 
        dialog.remove(); 
        action();
    });
    raiseToHighestZindex($(dialog)).appendTo("body");
}

var dialogs = {
    /* Create stream */
    createStream: function () {
        var dialog = $("#myOwnCreateStreamTemplate").tmpl();
        dialog.find("._close-question").on("click", function () { 
            dialog.remove();
        });
        
        var select = dialog.find(".rm-form-input-select");
        for(var i = 0; i < mor.categories.length; i ++) {
            var option = $("<option>").val(mor.categories[i].id).text(mor.categories[i].name);
            if(i === 12) {
                option.attr("selected", "selected");
            }
            option.appendTo(select);
        }

        dialog.find("._accept-question").on("click", function (event) { 
            // Validate data
            var form = dialog.find(":input");
            dialog.find(".rm-gui-alert-error").hide();

            // Create stream
            $.post("/api/v2/stream/createStream", form.serialize(), function(json) {
                if(json.status === 1) {
                    dialog.remove();
                    callModuleFunction("streams.loadFromData", json.jobs);
                } else {
                    dialog.find(".rm-gui-alert-error").show().text(json.message);
                }
            });
        });
        raiseToHighestZindex($(dialog)).appendTo("body");
    },
    changePassword: function() {
        var dialog = $("#myOwnChangePassword").tmpl();
        dialog.find("._close-question").on("click", function() { 
            dialog.remove();
        });
        dialog.find("._accept-question").on("click", function(event) {
            var form = dialog.find(":input");
            
            // Check old password
            var old = form.filter("#old");
            if(typeof old.attr("disabled") === "undefined") {
                if(old.val().length < 3 || old.val().length > 32) {
                    dialog.find(".rm-form-field-validate#old").addClass("visible").text("Password must contain from 3 to 32 chars");
                    return;
                } else {
                    dialog.find(".rm-form-field-validate#old").removeClass("visible");
                }
            } else {
                dialog.find(".rm-form-field-validate#old").removeClass("visible");
            }
            
            // Check password length
            var new1 = form.filter("#new1").val();
            if(new1.length < 3 || new1.length > 32) {
                dialog.find(".rm-form-field-validate#new1").addClass("visible").text("Password must contain from 3 to 32 chars");
                return;
            } else {
                dialog.find(".rm-form-field-validate#new1").removeClass("visible");
            }
            
            // Check password match
            var new2 = form.filter("#new2").val();
            if(new1 != new2) {
                dialog.find(".rm-form-field-validate#new2").addClass("visible").text("Passwords mismatch");
                return;
            } else {
                dialog.find(".rm-form-field-validate#new2").removeClass("visible");
            }
            
            // Create request
            $.post("/radiomanager/changePassword", form.serialize(), function(data) {
                try {
                    var json = JSON.parse(data);
                } catch(e) {
                    return;
                }
                if(json.code === "SUCCESS") {
                    mor.user_permanent = 1;
                    dialog.remove();
                }
                showPopup(lang.conv(json.code, "user.password"));
            });
            
        });
        raiseToHighestZindex($(dialog)).appendTo("body");
    }
};

raiseToHighestZindex = function(elem) {
    var highest_index = 0;
    $("*").each(function() {
        var cur_zindex= $(this).css("z-index");
        if (cur_zindex > highest_index) {
            highest_index = cur_zindex;
            $(elem).css("z-index", cur_zindex + 1);
        }
    });
    return $(elem);
};


