(function($, w, j) {
    
    $(document).on("ready", function () {
        //initStatus(0);
    });

    function initStatus(timeout) {
        
        w.setTimeout(function() {

            $.post("/radiomanager/eventListen", { s: mor.last_event }, function(data) {
                    var json = filterAJAXResponce(data);
                    // Code here
                    var eventData = json.data.EVENTS;
                    for (var i in eventData) {
                        var ev = eventData[i];
                        switch(ev.event_type) {
                            case 'LORES_CHANGED':
                                //callModuleFunction("tracklist.trackChangeState", ev.event_target, ev.event_value); 
                                break;
                            case 'TRACK_INFO_CHANGED':
                                //callModuleFunction("tracklist.trackUpdate", ev.event_target);
                                break;
                            case 'TRACK_DELETED':
                                //callModuleFunction("tracklist.trackDelete", ev.event_target);
                                //mor.tracks_count --;
                                break;
                            case 'TRACK_ADDED':
                                //callModuleFunction("tracklist.trackAdd", ev.event_target);
                                //mor.tracks_count ++;
                                break;
                            case 'STREAM_DELETED':
                                callModuleFunction("streams.deleteStream", ev.event_target);
                                mor.streams_count --;
                                break;
                            case 'STREAM_ADDED':
                                callModuleFunction("streams.addStream", ev.event_target);
                                mor.streams_count ++;
                                break;
                            case 'STREAM_TRACKS_CHANGED':
                                //eventUpdateStream(ev.event_target);
                                callModuleFunction("streams.setTrackCount", ev.event_target, ev.event_value);
                                break;
                            case 'STREAM_TRACK_ADDED':
                                //eventUpdateStream(ev.event_target);
                                break;
                            case 'STREAM_TRACK_DELETED':
                                //callModuleFunction("tracklist.removeFromStream", ev.event_value);
                                break;
                            case 'STREAM_SET_CURRENT':
                                callModuleFunction("tracklist.setNowPlaying", ev.event_value, ev.event_target);
                                break;
                            case 'STREAM_SORT':
                                callModuleFunction("tracklist.setNewIndex", ev.event_target, ev.event_value);
                                break;
                            case 'TOKEN_REMOVE':
                                if(ev.event_value === mor.user_token) {
                                    redirectLogin();
                                }
                                break;
                            case 'LIB_DURATION_CHANGED':
                                mor.tracks_duration = ev.event_value;
                                break;
                            case 'STREAM_SORTED':
                                callModuleFunction("stream.reload", ev.event_target);
                                break;
                            case 'STREAM_UPDATED':
                                callModuleFunction("streams.updateStream", ev.event_target);
                                break;
                        }
                    }
                    try { updateRadioManagerInterface() } catch(e) {}
                    mor.last_event = json.data.LAST_EVENT_ID;
                    initStatus(0);
            })
                    .error(function()
                    {
                        initStatus(1000);
                    });
        }, timeout);
    }

})(jQuery, window, JSON);
