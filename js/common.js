// myownradio.biz common javascript functions

// Interface tricks
$("[auto-time]").livequery(function() {
    $(this).text(secondsToHms($(this).attr("auto-time")));
});

$(".dynTop, .-rm-center").livequery(function() {
    var w = $(this).width();
    var h = $(this).height();
    //$(this).width(w + w % 2).height(h + h % 2);
});

// jQuery extenstions
$.fn.extend({
    shuffle: function() {
        var items = $(this).children();
        for (var i = 0; i < items.length; i++)
        {
            var s = Math.round(Math.random() * items.length);
            items.eq(s).appendTo($(this));
        }
    }
});

$.fn.extend({
    switch : function(args) {
        var defs = {
            value: args.val || false,
            sw: args.sw || function() {
            }
        };

        $(this).addClass("mor_ui_switch");

        if (defs.value)
        {
            $(this).addClass("on");
        }

        $("<div>").addClass("rm_ui_switch_handle").appendTo($(this));

        $(this).bind("click", function() {
            $(this).toggleClass("on");
            var newValue = $(this).hasClass("on");
            defs.sw(newValue);
        });
    }
});

// Trick for post authorization by header
(function (token) {
    $.ajaxSetup({
        headers: { 'My-Own-Token': token },
        complete: function(data, textStatus, jqXHR) {
            if (typeof data.responseJSON === "object") {
                var json = data.responseJSON;
                if (typeof json.status !== "undefined" && typeof json.message !== "undefined" && typeof json.context !== "undefined") {
                    if (json.status === 0 && json.context === null) {
                        console.error("Error: " + json.message);
                    }
                }
            }
        }
    });
})(mor.user_token);

