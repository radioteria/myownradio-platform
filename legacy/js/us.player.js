/* Audio Player Section */

var radioStatus = false;

(function() {


    var radioPosition = 0;
    var radioStarted = false;
    
    var delayedStart = false;

    $(document).on("ready", function() {
        $("<div>").attr("id", "jplayer").appendTo("body");
        initRadioPlayer();
    });

    function initRadioPlayer() {
        $("#logger").text("Client-Server time delta: " + timeDifference + " ms");
        $("#jplayer").jPlayer({
            ready: function(event) {
                if(window.location.hash === '#play') {
                    startStream();
                }
            },
            ended: function(event) {
                stopStream();
            },
            error: function(event) {
                errorStream();
            },
            timeupdate: function(event) {
                radioPosition = radioStarted + (event.jPlayer.status.currentTime * 1000) + timeDifference;

                if(event.jPlayer.status.currentTime > 0 && ! connectEvent) {
                    connected();
                }
            },
            progress: function(event) {
            },
            swfPath: "/swf",
            supplied: "mp3",
            solution: "flash, html",
            volume: 1
        });
    }

    function startStream() {
        if(delayedStart) {
            window.clearTimeout(delayedStart);
        }
            
        connectEvent = false;
        radioStatus = true;
        radioStarted = Math.floor(new Date().getTime());

        $("#jplayer").jPlayer("setMedia", {
            mp3: "http://" + window.location.host + ":7778/audio?s=" + stream.stream_id
        }).jPlayer("play");

        updateCurrentTrack(true);

        $(".page-player-background").removeClass("awaiting");
        $(".page-player-title-nowplaying").text("CONNECTING...");
        $(".page-player-control-button").removeClass("icon-play").addClass("icon-stop");
    }

    function stopStream() {
        connectEvent = false;
        radioStatus = false;
        $("#jplayer").jPlayer("stop").jPlayer("clearMedia");
        $(".page-player-background").addClass("stopped awaiting");
        $(".page-player-title-nowplaying").text("STOPPED");
        $(".page-player-control-button").addClass("icon-play").removeClass("icon-stop");
        $(".page-player-title-track").text("NONE");
        $(".page-player-title-progress-value").width(0);
        radioMicroSync = 0;
    }
    
    function errorStream() {
        connectEvent = false;
        radioStatus = false;
        $("#jplayer").jPlayer("stop").jPlayer("clearMedia");
        $(".page-player-background").addClass("stopped awaiting");
        $(".page-player-title-nowplaying").text("STREAM ERROR");
        $(".page-player-control-button").addClass("icon-play").removeClass("icon-stop");
        $(".page-player-title-track").text("NONE");
        $(".page-player-title-progress-value").width(0);
        radioMicroSync = 0;
        delayedStart = window.setTimeout(function(){
            delayedStart = false;
            startStream();
        }, 5000);
    }
    
    var connectEvent = false;
    function connected() {
        connectEvent = true;
        $(".page-player-background").removeClass("stopped");
        $(".page-player-title-nowplaying").text("NOW PLAYING");
    }

    var refreshHandle = false;
    var iteratorCount = 0;
    
    function statusRefresh() {
        if(radioStatus === false || typeof myRadio === "undefined") 
            return false;
        
        // Update interface
        if(typeof myRadio.data.now_playing !== "undefined") {
            var realPos = radioPosition - myRadio.data.now_playing.started_at;
            var perCent = 100 / myRadio.data.now_playing.duration * realPos;
            if(perCent < 0 || perCent > 100) {
                $(".page-player-title-progress-value").hide();
            } else {
                $(".page-player-title-progress-value").width(perCent.toString() + "%").show();
            }
        } else {
            $(".page-player-title-progress-value").width("0%").hide();
        }
        
        if (iteratorCount > 40) {
            updateCurrentTrack();
        } else {
            iteratorCount ++;
            refreshHandle = window.setTimeout(function() { statusRefresh(); }, 250);
        }
    }

    function updateCurrentTrack(sync)
    {

        $.post("/api/v2/stream", {id: stream.stream_id, time: radioPosition - streamPreload}, function(json) {
            iteratorCount = 0;
            myRadio = json;
            if(myRadio.data.stream_status === 1 && typeof myRadio.data.now_playing !== "undefined") {
                var titleUpperCase = myRadio.data.now_playing.title.toUpperCase();
                var nextTitleUpperCase = myRadio.data.next.title.toUpperCase();
            } else {
                var titleUpperCase = "NO SIGNAL";
                var nextTitleUpperCase = "NO SIGNAL";
            }

            $(".page-player-title-track").fadeText(titleUpperCase);
            $(".page-player-title-next-track").fadeText(nextTitleUpperCase);
            
        }).complete(function(){
            statusRefresh();
        });
    }

    $("._player-status-toggle").live("click", function() {
        if (radioStatus)
            stopStream();
        else
            startStream();
    });

})();


// Common links
function removeTrackTotally() {
    
    if(radioStatus === false)
        return false;
    
    var ret = confirm("Are you sure want to completely remove this track from all your streams and profile?");
    if(ret) {
        var stream_id   = myRadio.stream_id;
        var track_id    = myRadio.track_id;

        $.post("/radiomanager/removeTrack", {
            track_id    : track_id
        }, function(json){
            if(json.code === "DELETE_SUCCESS")
            {
                $(".page-player-title-track").fadeText("WAIT...");
            }
            else
            {
                alert(data.code);
            }
        });
    }
    
    return false;
    
}

function removeTrackFromStream() {
    
    if(radioStatus === false || typeof myRadio.unique_id === "undefined")
        return false;
    
    var ret = confirm("Are you sure want to remove this track from stream?");
    if(ret) {
        var stream_id   = myRadio.stream_id;
        var unique_id   = myRadio.unique_id;
        var token       = $("body").attr("token");
        
        $.post("/radiomanager/removeTrackFromStream", {
            authtoken   : token,
            unique_id   : unique_id,
            stream_id   : stream_id
        }, function(data){
            try
            {
                var json = JSON.parse(data);
                if(json.code === "REMOVE_FROM_STREAM_SUCCESS")
                {
                    $(".page-player-title-track").fadeText("WAIT...");
                }
                else
                {
                    alert(data.code);
                }
            }
            catch(e){}
        });
    }
    
    return false;
    
}
