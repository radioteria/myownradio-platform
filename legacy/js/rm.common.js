var radiomanager = {
    updateCounters: function() {
        $(".profile-tracks-count").text(user.user_stats.user_tracks_count);
        $(".profile-tracks-time").text(secondsToHms(user.user_stats.user_tracks_time));
        if (user.plan_data.plan_time_limit === 0) {
            $("#total_time_left").html("&infin;");
            $(".rm_infobar_progress #handle").width("100%");
            $(".rm_infobar_progress #cents").text("100%");
            $(".rm_infobar_progress").removeClass("over");
        } else if (user.plan_data.plan_time_limit < user.user_stats.user_tracks_time) {
            $("#total_time_left").html(secondsToHms(0));
            $(".rm_infobar_progress #handle").width("100%");
            $(".rm_infobar_progress #cents").text(">100%");
            $(".rm_infobar_progress").addClass("over");
        } else {
            $("#total_time_left").text(secondsToHms(user.plan_data.plan_time_limit - user.user_stats.user_tracks_time));
            $(".rm_infobar_progress #handle").width((100 / user.plan_data.plan_time_limit * user.user_stats.user_tracks_time).toString() + "%");
            $(".rm_infobar_progress #cents").text(Math.floor(100 / user.plan_data.plan_time_limit * user.user_stats.user_tracks_time).toString() + "%");
            $(".rm_infobar_progress").removeClass("over");
        } 
    }
};

var stream = {
    shuffle: function(stream_id) {
        $.post("/api/v2/stream/shuffleStream", {
            id : stream_id
        }, function(data) {
            if (data.status === 1) {
                callModuleFunction("stream.reload", stream_id);
            }
        });
        return stream;
    },
    sort: function(stream_id, target, index) {
        $.post("/api/v2/stream/moveTrack", {
            id          : stream_id,
            unique_id   : target,
            new_index   : index + 1
        }, function(data) {
            console.log(data);
        });
        return stream;
    },
    reload: function(stream_id) {
        if(typeof active_stream === "undefined") {
            return;
        }
        if(active_stream.stream_id === stream_id) {
            ajaxGetTrackUniversal(true);
        }
    },
    state: function(stream_id) {
        $.post("/api/v2/stream/switchState", {
            id : stream_id
        }, function(json){
            //var json = filterAJAXResponce(data);
            //showPopup(lang.conv(json.code, "stream.state"));
            if (json.status === 1) {
                showPopup("Stream state changed");
            }
        });
    },
    purge: function(stream_id) {
        myOwnQuestion("Are you sure want to purge all tracks from selected stream?", function() {
            $.post("/api/v2/stream/purgeStream", { id : stream_id }, function(json) {
                if (json.status === 1) {
                    showPopup("Stream purged successfully");
                }
            });
        });
    },
    delete: function(stream_id) {
        myOwnQuestion("Are you sure want to delete selected stream?", function() {
            $.post("/api/v2/stream/deleteStream", { id : stream_id }, function(data){
                if (json.status === 1) {
                    showPopup("Stream deleted successfully");
                }
            });
        });
    },
    play: function(stream_id, unique_id) {
        $.post("/api/v2/stream/playFrom", {
            'id'        : stream_id,
            'unique_id' : unique_id
        }, function(data) {
            if (data.status === 1) {
                showPopup("Current playing track changed successfully");
            }
        });
    }
};


function updateRadioManagerInterface() {
    callModuleFunction("radiomanager.updateCounters");
}

$(document).on("ready", function(){
    $(".rm_body_wrap").height($("body").height() - 65);
    
    radiomanager.updateCounters();
    
    // autopopup
    switch(window.location.hash) {
        case '#password':
            callModuleFunction("dialogs.changePassword");
            break;
    }
});

$(window).on("resize", function(){
    $(".rm_body_wrap").height($("body").height() - 65);
});

function filterAJAXResponce(jsonDATA)
{
    try
    {
        if(jsonDATA.error === undefined)
        {
            return jsonDATA;
        }
        if(jsonDATA.error === "ERROR_UNAUTHORIZED")
        {
            redirectLogin();
            return jsonDATA;
        }
    }
    catch(e)
    {
        myMessageBox("Wrong server responce: " . responce);
    }
}
