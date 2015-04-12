$.fn.extend({
    rmpicker: function() {
        
        var initial = $(this);
        var getter = $(this).attr("data-url");
        var list = $(this).text();
        var ro = ($(this).attr("read-only") !== undefined) ? true : false;
        
        var timer = false;
        
        var helper = $("#rmGuiPickerHepler").tmpl();
        var search = $("<span>")
            .attr("contentEditable", true)
            .addClass("rm-gui-picker-search")
            .bind('textchange', function(event){
                var req = $(this).text();
                var vars = helper.find(".-variants-block .-variants");
                
                if (timer !== false) {
                    window.clearInterval(timer);
                }
                timer = window.setTimeout(function(){
                    timer = false;
                    helper.show();
                    $.post("/api/v2/tags/getList", {
                        s: req
                    }, function(json){
                        vars.children().remove();
                        if (json.status === 1) {
                            if (json.data.length === 0) {
                                helper.find(".rm-gui-picker-empty").show();
                            } else {
                                helper.find(".rm-gui-picker-empty").hide();
                                json.data.forEach(function(item){
                                    vars.append($("<li>")
                                            .attr("data-value", item.genre)
                                            .attr("data-id", item.id)
                                            .text(item.genre))
                                });
                            }
                        }
                        if (!ro) {
                            vars.append($("<li>").attr("data-value", req).text("Add \"" + req + "\""))
                        }
                    });
                }, 250);
            });

        helper.find(".-variants-block .-variants > li").livequery("click", function(event){
            event.stopPropagation();
            var editor = initial.find(".rm-gui-picker-search");
            $("<span>")
                .addClass("rm-gui-picker-item")
                .text($(this).attr("data-value"))
                .append($("<input>")
                    .attr("type", "hidden")
                    .attr("name", "genre[]")
                    .val($(this).attr("data-id")))
                .append($("<img>")
                    .attr("src", "/images/iconCloseWhite.png")
                    .addClass("rm-gui-picker-item-close")
                    .on('click', function(event){
                        var targ = $(this).parent();
                        targ.stop().animate({opacity:0}, 250, function(){
                            targ.remove();
                            editor.focus();
                        });
                    }))
                .insertBefore(editor);
            editor.text("").focus();
            helper.hide();
        });
        
        initial
            .live('focusin', function(){ $(this).addClass("focused"); })
            .live('focusout', function(){ $(this).removeClass('focused'); });
        
        
        $("body").on("click", function(event){
            helper.hide();
        });

        search.appendTo(initial);
        helper.insertAfter(initial);

    }
    
});

$(".rm-gui-picker").livequery(function(){
    $(this).rmpicker();
});
