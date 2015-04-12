
function myMessageBox(message, callback) {
    var item = $("#messageBoxTemplate").tmpl({message:message});
    callback = callback || function() {};
    item.find(".rm_mbox_btn_close").bind("click", function () { item.remove(); callback(); });
    item.find(".rm_mbox_close_btn").bind("click", function () { item.remove(); callback(); });
    item.appendTo("body");
}

function myQuestionBox(message, callback) {
    var item = $("#questionBoxTemplate").tmpl({message:message});
    callback = callback || function() {};
    item.find(".rm_mbox_btn_close").bind("click", function () { item.remove(); });
    item.find(".rm_mbox_close_btn").bind("click", function () { item.remove(); });
    item.find(".rm_mbox_btn_action").bind("click", function () { callback(); item.remove();  });
    item.appendTo("body");
}

function createForm(pattern, params, w, h)
{
    $(".rm_popup_form_background").remove();

        var wrap = $("<div>")
                .addClass("rm_popup_form_background")
                .bind('mousewheel', function() {
                    return false;
                });

        $("<div>")
                .addClass("rm_popup_form")
                .html($(pattern).tmpl())
                .appendTo(wrap);

        wrap
                .appendTo("body")
                .find(".rm_window_close_wrap")
                .unbind('click')
                .bind('click', function()
                {
                    formDestroy();
                });
}


function formDestroy()
{
    $(".rm_popup_form_background").remove();
}
