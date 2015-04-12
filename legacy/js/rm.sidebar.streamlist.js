var streams = {
    setTrackCount: function(stream_id, track_count) {
        return streams.getById(stream_id).find(".rm-streams-badge").text(track_count);
    },
    streamExists: function(stream_id) {
        return $(".rm_streamlist > li[data-stream-id='" + stream_id + "']").length > 0;
    },
    getById: function(stream_id) {
        return $(".rm_streamlist > li[data-stream-id='" + stream_id + "']");
    },
    deleteStream: function(stream_id) {
        return streams.getById(stream_id).remove();
    },
    addStream: function(stream_id) {
        if (streams.getById(stream_id).length !== 0) return;
        $.post("/radiomanager/api/getStreamItem", { stream_id: stream_id }, function(data) {
            try {
                var json = JSON.parse(data);
                streams.loadFromData(json);
            } catch(e) {}
        });
    },
    updateStream: function(stream_id) {
        if(streams.getById(stream_id).length === 0) return;
        $.post("/radiomanager/api/getStreamItem", { stream_id: stream_id }, function(data) {
            try {
                var json = JSON.parse(data);
                streams.updateFromData(stream_id, json);
            } catch(e) {}
        });
    },
    loadFromData: function(data) {
        $("#streamTemplate").tmpl(data).appendTo(".rm_streamlist");
    },
    updateFromData: function(stream_id, data) {
        streams.getById(stream_id).replaceWith($("#streamTemplate").tmpl(data));
    },
    setStatus: function(stream_id, status) {
        streams.getById(stream_id).attr("data-state", status);
    }
};

$(document).ready(function()
{
    $(".track-accept.stream").livequery(function() {
        $(this).droppable({
            drop: function(event, ui) {
                var stream_id = $(this).attr('data-stream-id');
                var track_id = ui.helper.attr('track-id');
                callModuleFunction("trackworks.addSelectionToStream", stream_id, track_id);
                $(this).toggleClass('selected', false);
            },
            over: function(event, ui) {
                $(this).toggleClass('selected', true);
            },
            out: function(event, ui) {
                $(this).toggleClass('selected', false);
            },
            accept: ":not(.rm_streamview) .rm_tracks_item",
            tolerance: "pointer"
        });
    });
    $(".rm_streams_data").livequery(function(){
        var data = JSON.parse(atob($(this).attr('content')));
        $(this).remove();
        streams.loadFromData(data);
    });
    $(".rm_streamlist > li").livequery(function(){
        // Context menu implementation
        $(this).on('contextmenu', function(event) {
            showStreamListMenu(this, event);
            event.preventDefault();
            return false;
        });
    });
});