var nowplaying = {
    update_interval_max: 10000,
    handle: false,
    previousTrack: null,
    currentTrack: null,
    sync: function(forced) {
        if(nowplaying.handle) {
            window.clearTimeout(nowplaying.handle);
        }
        $.post("/streamStatus", { 
            stream_id : active_stream.stream_id
        }, function(data) {
            var json = filterAJAXResponce(data);
            var nextIn;
            if(json.stream_status === 1) {
                nextIn = json.time_left || 5000 > nowplaying.update_interval_max ? nowplaying.update_interval_max : json.time_left || 5000;
                nowplaying.currentTrack = json;
            } else {
                nextIn = 5000;
                nowplaying.currentTrack = null;
            }
            nowplaying.update(forced).handle = window.setTimeout(function() {
                nowplaying.handle = false;
                nowplaying.sync();
            }, nextIn);
        });
        return nowplaying;
    },
    update: function(forced) {
        forced = forced || false;

        if((nowplaying.currentTrack !== null) && (nowplaying.currentTrack.stream_status === 1)) {
            $(".rm-stream-switch:not(.active)").addClass("active");
        } else {
            tracklist.nothingPlaying();
            $(".rm-stream-switch.active").removeClass("active");
            $(".rm_status_wrap .ttl").text("Stream stopped");
            return nowplaying;
        }
        if(forced || (nowplaying.previousTrack === null) || (nowplaying.previousTrack.unique_id !== nowplaying.currentTrack.unique_id)) {
            $(".rm_status_wrap .ttl").text(nowplaying.currentTrack.t_order + ". " + nowplaying.currentTrack.now_playing);
            tracklist.setNowPlaying(nowplaying.currentTrack.unique_id);
            nowplaying.previousTrack = nowplaying.currentTrack;
        }
        return nowplaying;
    }
};

$(document).on("ready", function() {
    if($("body").hasClass("stream")) {
        nowplaying.sync();
        $(".rm-stream-switch").live("click", function(e){
            callModuleFunction("stream.state", active_stream.stream_id);
        });
        $(".rm-shuffle-button").live("click", function(e){
            callModuleFunction("stream.shuffle", active_stream.stream_id);
        });
    }
});


