function secondsToHandM(time)
{
    var hours = Math.floor(time / 1000 / 3600);
    var minutes = Math.floor(time / 1000 / 60) % 60;

    var out = "";

    (hours > 0) ? out += hours.toString() + " hour(s) and " : null;
    out += minutes.toString() + " minute(s)";

    return out;
}

function secondsToHms(sec)
{
    if(sec < 0)
    {
        return "-";
    }
    
    var hours = Math.floor(sec / 1000 / 3600);
    var minutes = Math.floor(sec / 1000 / 60) % 60;
    var seconds = Math.floor(sec / 1000) % 60;
    
    var out = "";
    
    if(hours)
        out += (hours > 9)   ? hours.toString() + ":"   : "0" + hours.toString() + ":";
    
    out += (minutes > 9) ? minutes.toString() + ":" : "0" + minutes.toString() + ":";
    out += (seconds > 9) ? seconds.toString() : "0" + seconds.toString();
    
    return out;
}

// Add remove event
(function() {
    var ev = new $.Event('remove'),
            orig = $.fn.remove;
    $.fn.remove = function() {
        $(this).trigger(ev);
        //return ;//orig.apply(this, arguments);
    };
})();

// Add increaser
(function() {
    $.fn.increment = function(attr, incr)
        {
            var ov = parseInt($(this).attr(attr));
            return $(this).attr(attr, ov + parseInt(incr));
        };
    
    
    $.fn.justtext = function() {
        var clone = $(this).clone();
            clone.children().remove();
        return clone
                .text().trim();
    };
    
    String.prototype.toInt = function()
    {
        return parseInt(this);
    };
})();

// Warn for input boxes
(function($) {
    $.fn.extend({
        validate: function() {
            var warns = 0;
            $(this).each(function() {
                if ($(this).filter("div").length > 0 && $(this).filter("div").serializeGenres().length === 0) {
                    blinkElement(this);
                    $(this).focus();
                    warns++;
                    return false;
                }
                if ($(this).filter("input, textarea").length > 0 && $(this).filter("input, textarea").val().length === 0) {
                    blinkElement(this);
                    $(this).focus();
                    warns++;
                    return false;
                }
            });
            return warns;

        },
        serializeGenres: function() {
            var genres = [];
            $(".rm_create_stream_genrelist > div")
                    .filter(":not(.placeholder)")
                    .each(function() {
                        if ($(this).text().length > 0)
                        {
                            genres.push($(this).text());
                        }
                    });
            return genres.join(", ");
        }
    });

    function blinkElement(el)
    {
        var borderColor = $(el).css("border-color");
        var backColor = $(el).css("background-color");
        var item = $(el);
        item.css({
            "background-color": "#fcc",
            "border-color": "#f00"
        });
        window.setTimeout(function() {
            item.css({
                "background-color": backColor,
                "border-color": borderColor
            });
        }, 250);
    }
})(jQuery);

Array.prototype.unique = function() {
    var a = this.concat();
    for(var i=0; i<a.length; ++i) {
        for(var j=i+1; j<a.length; ++j) {
            if(a[i] === a[j])
                a.splice(j--, 1);
        }
    }

    return a;
};

function redirectHome()
{
    window.location = "/";
}

function redirectLogin()
{
    window.location = "/login";
}

$.fn.extend({
    fadeText: function(text)
    {
        if($(this).text() !== text)
        {
            $(this).stop().animate({opacity:0}, 250, function(){
                $(this).text(text)
                    .animate({opacity:1}, 250);
            });
        }
    }
});
