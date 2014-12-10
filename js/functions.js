function secondsToHms(sec)
{
    if (sec < 0)
    {
        return "-";
    }

    var hours = Math.floor(sec / 3600);
    var minutes = Math.floor(sec / 60) % 60;
    var seconds = sec % 60;

    var out = "";

    if (hours > 0) {
        out += (hours > 9) ? hours.toString() + ":" : "0" + hours.toString() + ":";
    }
    
    out += (minutes > 9) ? minutes.toString() + ":" : "0" + minutes.toString() + ":";
    out += (seconds > 9) ? seconds.toString() : "0" + seconds.toString();

    return out;
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
