var tracklist = {
    /* Tracklist common methods */
    trackDelete: function(track_id) {
        tracklist.getById(track_id).remove();
        tracklist.renumberTracks();
        tracklist.updateSelection();
        $(document).scroll();
        return tracklist;
    },
    trackChangeState: function(track_id, value) {
        tracklist.getById(track_id).attr('low-state', value);
        return tracklist;
    },
    trackAdd: function(track_id, data) {
        data = data || false;

        if (tracklist.trackExists(track_id) === true) return false;

        if(data === false) {
            $.post("/radiomanager/api/getTrackItem", {
                track_id: track_id, 
                type: "json"
            }, function(json) {
                try {
                    tracklist.trackAdd(track_id, json);
                } catch(e) {}
            });
        } else {
            $("#streamTrackTemplate").tmpl(data).prependTo(".rm_tracks_body:not(.rm_streamview)");
            tracklist.renumberTracks();
        }
        return tracklist;
    },
    trackUpdate: function(track_id, data) {
        data = data || false;
        if(tracklist.trackExists(track_id) === false) return false;
        if(data === false) {
            $.post("/radiomanager/api/getTrackItem", {
                track_id: track_id, 
                type: "json" 
            }, function(json) {
                try {
                    tracklist.trackUpdate(track_id, json);
                } catch(e) {}
            });
        } else {
            var elem = tracklist.getById(track_id);
            var attributes = elem.prop("attributes");
            var target = $("#streamTrackTemplate").tmpl(data);

            $.each(attributes, function () {
                target.attr(this.name, this.value);
            });

            elem.replaceWith(target);
            tracklist.updateSelection().renumberTracks();
        }
        return tracklist;
    },
    
    /* Tracklist in stream view methods */
    removeFromStream: function(unique_id) {
        tracklist.getByUnique(unique_id).remove();
        tracklist.updateSelection().renumberTracks();
        $(document).scroll();
        return tracklist;
    },
    setNowPlaying: function(unique_id, stream_id) {
        if(typeof active_stream === "undefined") { 
            return; 
        }
        stream_id = stream_id || active_stream.stream_id;
        if(parseInt(stream_id) !== parseInt(active_stream.stream_id)) { 
            return; 
        }
        tracklist.nothingPlaying().getByUnique(unique_id).addClass("nowplaying");
        return tracklist;
    },
    nothingPlaying: function() {
        $(".rm_streamview .rm_tracks_item.nowplaying").removeClass("nowplaying");
        return tracklist;
    },
    setNewIndex: function(unique_id, index) {
        var element = tracklist.getByUnique(unique_id);
        if(element.index() !== index - 1) {
            var badge = element.appendTo("<div>");
            var e = $(".rm_streamview .rm_tracks_item").eq(index - 1);
            badge.insertBefore(e);
            tracklist.renumberTracks();
        }
        return tracklist;
    },
    
    /* Special helper methods */
    trackExists: function(track_id) {
        return tracklist.getById(track_id).length > 0;
    },
    getById: function(track_id) {
        return $(".rm_tracks_item[track-id='" + track_id + "']");
    },
    getByUnique: function(unique_id) {
        return $(".rm_streamview .rm_tracks_item[data-unique='" + unique_id + "']");
    },
    
    /* Data organization methods */
    renumberTracks: function() {
        $(".rm_tracks_item").each(function(i) {
            $(this).find("div").eq(1).html(i+1);
            if (i % 2 === 0) {
                $(this).removeClass("odd");
            } else {
                $(this).addClass("odd");
            }
        });
        return tracklist;
    },
    updateSelection: function() {
        var selected = $(".rm_tracks_item.selected");

        var selectionCount = selected.length;
        var selectionTime = 0;
    
        selected.each(function() {
            selectionTime += parseInt($(this).attr('track-duration'));
        });

        $("#sel_tracks_count").text(selectionCount);
        $("#sel_tracks_time").text(secondsToHms(selectionTime));
    
        if(selectionCount > 0) {
            $(".rm_status_wrap").addClass("selected");
        } else {
            $(".rm_status_wrap").removeClass("selected");
        }
        return tracklist;
    },
    selectAll: function() {
        $(".rm_tracks_item[low-state='1']")
            .addClass("selected")
            .removeClass("active");
        $(".rm_tracks_item[low-state='1']:last-child")
            .addClass("active");
        tracklist.updateSelection();
        return tracklist;
    },
    invertSelection: function() {
        $(".rm_tracks_item[low-state='1']")
            .toggleClass("selected");
        tracklist.updateSelection();
        return tracklist;
    },
    noSelection: function() {
        $(".rm_tracks_item")
            .removeClass("selected")
            .removeClass("active");
        tracklist.updateSelection();
        return tracklist;
    },
    clearAll: function() {
        $(".rm_tracks_item").remove();
    },
    getSelected: function() {
        return $(".rm_tracks_item.selected").map(function () { return $(this).attr("track-id"); }).toArray().join(",");
    }
};

var trackworks = {
    killSelection: function() {
        myOwnQuestion("Are you sure want to delete selected tracks from account?", function() {
            var selected_ids = $(".rm_tracks_item.selected").map(function(){return $(this).attr("track-id");}).toArray().join(",");
            $.post("/radiomanager/removeTrack", { track_id : selected_ids }, function (data) {
                var json = filterAJAXResponce(data);
                showPopup(lang.conv(json.code, "track.delete"));
                if(json.code === "SUCCESS" && typeof json.data === "object")
                {
                    json.data.forEach(function(e) {
                        if(e.result === "SUCCESS")
                        {
                            tracklist.trackDelete(e.value);
                        }
                    });
                    try { updateRadioManagerInterface() } catch(e) {}
                }
            });
        });
        return trackworks;
    },
    removeSelectionFromStream: function() {
        myOwnQuestion("Are you sure want to remove selected tracks from stream?", function() {
            var selected_ids = $(".rm_tracks_item.selected").map(function(){return $(this).attr("data-unique");}).toArray();
            var stream_id = active_stream.stream_id;
            stopPlayer();
            $.post("/api/v2/stream/removeTracks", {
                id      : stream_id,
                tracks  : selected_ids.join(",")
            }, function(data) {
                if(data.status === 1)
                {
                    selected_ids.forEach(function(e) {
                        tracklist.removeFromStream(e);
                    });
                }
            });
        });
        return trackworks;
    },
    addSelectionToStream: function(stream_id, track_ids) {
        track_ids = track_ids || false;
        if(track_ids === false) {
            var selected = $(".rm_tracks_item.selected[low-state='1']");
            if(selected.length === 0) { return; }
            track_ids = selected.map(function(){return $(this).attr("track-id");}).toArray().join(",");
        }
        $.post("/api/v2/stream/addTracks", {
            id      : stream_id,
            tracks  : track_ids
        }, function(data) {
            if (data.status === 1) {
                showPopup("Tracks added to stream successfully");
            }
        });
    },
    tagEditor: function() {
        var track_id = tracklist.getSelected();
        if(track_id) {
            showTagEditorBox(track_id);
        }
    }
};


function ajaxGetTrackUniversal(replace)
{
    $("body").addClass("ajaxBusy");
    var lastTrack = $(".rm_tracks_item").length;
    $.post("", { 
        from      : replace || false ? 0 : lastTrack,
        filter    : $("#filterBox").val(),
    }, function(json){
        if(replace || false === true)
        {
            $("body").addClass("partial");
            callModuleFunction("tracklist.clearAll");
        }
        if(json.length < 50)
        {
            $("body").removeClass("partial");
        }
        $("#streamTrackTemplate").tmpl(json).appendTo(".rm_tracks_body");
        tracklist.renumberTracks();
        callModuleFunction("nowplaying.update", true);
        $("body").removeClass("ajaxBusy");
    });
}

/* Tracklist Initialization */
$(document).on('ready', function() {

    $(".rm_tracks_wrap").on("scroll", function(){
        if($("body").hasClass("partial") === false) {
            return;
        }
        if($("body").hasClass("ajaxBusy")) { 
            return; 
        }
        
        var bottom = $(".rm_tracks_table").height() - ($(".rm_tracks_wrap").scrollTop() + $(".rm_tracks_wrap").height());
        
        if(bottom >= 400) {
            return;
        }
        
        ajaxGetTrackUniversal();
    });
    
    tracklist.updateSelection();

    // Load first pack of tracks from encoded array    
    $(".rm_tracks_data").livequery(function() {
        var data = JSON.parse(atob($(this).attr('content')));
        $(this).remove();
        $("#streamTrackTemplate").tmpl(data).appendTo(".rm_tracks_body");
        tracklist.renumberTracks();
    });

});

/* Tracklist Model Methods */
(function() {
    // Stream View sort implementation
    $(".rm_tracks_body.rm_streamview").livequery(function(event){
        $(this).sortable({
            items: ".rm_tracks_item:visible",
            stop: function( event, ui ) {
                var this_element = ui.item.attr("data-unique");
                var this_index = $(ui.item).index();
                var stream_id = active_stream.stream_id;
                stream.sort(stream_id, this_element, this_index);
                tracklist.renumberTracks();
            }
        });
    }); 
    
    // Library view drag and drop implementation
    $(".rm_tracks_body:not(.rm_streamview) .rm_tracks_item").livequery(function(event) {
        $(this).draggable({
            cursor: "move",
            appendTo: 'body',
            cursorAt: {top: 8, left: 8},
            containment: 'window',
            helper: function() {
                if($(this).hasClass("selected") === false)
                {
                    $(".rm_tracks_item").removeClass("selected active");
                    $(this).addClass("selected active");
                }
                var selected = $(".rm_tracks_item.selected");
                var selected_ids = selected.map(function(){ return $(this).attr("track-id"); }).toArray();
                var caption = (selected.length > 1) ? (selected.length + " track(s)") : ("<b>" + selected.find("div").eq(2).text() + "</b> - <b>" + selected.find("div").eq(1).text() + "</b>");
                return $("<div>")
                        .attr("track-id", selected_ids.join(","))
                        .addClass("rm_track_drag")
                        .html("Selected " + caption);
            }
        });
    });
 
    // Click outside of list unselects all
    $("html").bind('click', function(event) {
        if ($(event.target).parents().andSelf().filter(".rm_tracks_table, .rm_mouse_menu_wrap, .rm_popup_form_background, .rm_mbox_shader").length === 0) {
            tracklist.noSelection();
        }
    })
    
    // Hotkeys for tracklist
    $(document).bind('keydown', function(event) {
        if (event.ctrlKey && event.keyCode === 65) {
            tracklist.selectAll();
        } else if (event.ctrlKey && event.keyCode === 73) {
            tracklist.invertSelection();
        } else if (event.ctrlKey && event.keyCode === 83) {
            var stream_id = active_stream.stream_id || false;
            if(stream_id !== false) {
                stream.shuffle(stream_id);
            }
        } else {
            return;
        }
        event.preventDefault();
    });

    // Tracklist items selectors
    $(".rm_tracks_item").livequery(function() {
        $(this)
                // Context menu implementation
                .live('contextmenu', function(event) {
                    // Context menu for selected tracks from library
                    if ($(":not(.rm_streamview) .rm_tracks_item.selected").length > 0) {
                        showTrackInTracklistMenu(event);
                    } 

                    // Context menu for selected tracks from stream
                    if ($(".rm_streamview .rm_tracks_item.selected").length > 0) {
                        showTrackInStreamMenu(event);
                    }
                    
                    event.preventDefault();
                    return false;
                })
                // Selection black magic
                .live('mouseup', function(event) {
                    if (event.button === 2 && $(this).hasClass("selected")) {
                        return;
                    }

                    var prevClicked = $(".rm_tracks_item.active").index();
                    var ctrlKey = event['ctrlKey'];
                    var shiftKey = event['shiftKey'];

                    if (ctrlKey === false) {
                        $(".rm_tracks_item").removeClass("selected");
                    }

                    $(".rm_tracks_item").removeClass("active");

                    $(this).addClass('active');

                    if (shiftKey === false || prevClicked === -1) {
                        $(this).toggleClass('selected');
                    } else {
                        var newClicked = $(this).index();

                        if (newClicked > prevClicked) {
                            $(".rm_tracks_item").slice(prevClicked, newClicked + 1).addClass('selected');
                        } else {
                            $(".rm_tracks_item").slice(newClicked, prevClicked + 1).addClass('selected');
                        }
                    }
                    tracklist.updateSelection();
                });

    });

})();

