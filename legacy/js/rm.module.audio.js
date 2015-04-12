(function(){
    
    
    $(window).ready(function(){
        $("#jplayer").jPlayer({
            ready: function(event) {
            },
            ended: function(event) {
                playerStopped();
            },
            error: function(event) {
                
            },
            timeupdate: function(event) {
            },
            progress: function(event) {
            },
            swfPath: "/swf",
            supplied: "mp3",
            solution: "flash,html",
            volume: 1
        });
    });
    
    $('.rm_tracks_item .rm-track-preview').live("click", function(event) {
        var track_id = $(this).parents(".rm_tracks_item").attr("track-id");
        if(track_id === now_playing) {
            console.log("Stop");
            stopPlayer();
            playerStopped();
        } else {
            console.log("Play");
            playerStopped();
            startPlayer(track_id);
        }
    });
    
    function playerStopped() {
        console.log("Stopped");
        $('.rm_tracks_item').find(".rm-track-preview").html('<i class="icon-play2 no-margin"></i>');
        now_playing = null;
    }
    
})();

var now_playing = null;

    function startPlayer(track_id) {
        var selected = tracklist.getById(track_id);
        var file = selected.find("input").val();
        selected.find(".rm-track-preview").html('<i class="icon-stop2 no-margin"></i>');
        $("#jplayer").jPlayer("setMedia", {mp3:file}).jPlayer("play");
        now_playing = track_id;
    }
    

    function stopPlayer() {
        
        $("#jplayer").jPlayer("stop").jPlayer("clearMedia");
        
    }