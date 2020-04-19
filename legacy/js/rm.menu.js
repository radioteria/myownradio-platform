// Menu for stream list
function showStreamListMenu(element, event) {
    var stream_id = $(element).attr("data-stream-id");
    var menu = [
        {
            name: '<i class="icon-switch"></i>Start/Stop stream',
            action: function() { callModuleFunction("stream.state", stream_id); }
        },
        {
            name: ''
        },
        {
            name: '<i class="icon-shuffle"></i>Shuffle stream',
            action: function() { callModuleFunction("stream.shuffle", stream_id); }
        },
        {   
            name: '<i class="icon-pencil"></i>Edit stream...',
            action: function() { }
        },
        {
            name: ''
        },
        {
            name: '<i class="icon-trash"></i>Purge stream',
            action: function() { callModuleFunction("stream.purge", stream_id); }
        },

        {
            name: '<i class="icon-cross"></i>Delete stream',
            action: function() { callModuleFunction("stream.delete", stream_id); }
        }
    ];
   
    var m = $("<div>")
        .addClass("rm_mouse_menu_wrap")
        .append(arrayToSubmenu(event, menu))
        .bind("click", function() { /* event.stopPropagation(); */ });

    createMenu(event, m, "body");
}


// Stream View Tracklist Menu
function showTrackInStreamMenu(e)
{
    var menu = [
        {
            name: $("<i>").addClass("icon-play2").get(0).outerHTML + "Play on Radio",
            action: function() {
                if($(".rm_tracks_item.active").length === 0) return;
                var unique_id = $(".rm_tracks_item.active").attr("data-unique");
                var stream_id = active_stream.stream_id;
                callModuleFunction("stream.play", stream_id, unique_id);
            }
        },
        {
            name: $("<i>").addClass("icon-pencil").get(0).outerHTML + "Metadata editor",
            enabled: $(".rm_tracks_item.active").length > 0,
            action: function() { callModuleFunction("trackworks.tagEditor"); }
        },
        {
            name: $("<i>").addClass("icon-plus").get(0).outerHTML + "Add selection to...",
            enabled: $(".rm_tracks_item.selected[low-state='1']").length > 0,
            submenu: showAddToStreamMenu()
        },
        {
            name: $("<i>").addClass("icon-trash").get(0).outerHTML + "Remove from stream",
            action: function() { callModuleFunction("trackworks.removeSelectionFromStream"); }
        }
    ];

    var m = $("<div>")
            .addClass("rm_mouse_menu_wrap")
            .append(arrayToSubmenu(e, menu))
            .bind("click", function() { /* event.stopPropagation(); */ });


    createMenu(e, m, "body");
}

// Library Tracklist Menu
function showTrackInTracklistMenu(e)
{

    var menu = [
        {
            name: $("<i>").addClass("icon-pencil").get(0).outerHTML + "Metadata editor",
            enabled: $(".rm_tracks_item.active").length > 0,
            action: function() { callModuleFunction("trackworks.tagEditor"); }
        },
        {
            name: $("<i>").addClass("icon-plus").get(0).outerHTML + "Add selection to...",
            enabled: $(".rm_tracks_item.selected[low-state='1']").length > 0,
            submenu: showAddToStreamMenu()
        },
        {
            name: $("<i>").addClass("icon-trash").get(0).outerHTML + "Delete selected track(s)",
            action: function() { callModuleFunction("trackworks.killSelection"); }
        }
    ];

    var m = $("<div>")
            .addClass("rm_mouse_menu_wrap")
            .append(arrayToSubmenu(e, menu))
            .bind("click", function() { /* event.stopPropagation(); */
            });


    createMenu(e, m, "body");
}



$(document).ready(function()
{
    $(this).bind("click", function() {
        hideTracklistMenu();
    });
});

function showAddToStreamMenu()
{
    var submenu = [];

    $("ul.rm_streamlist > li").each(function()
    {
        (function(sid, name) {
            submenu.push({
                name: '<i class="icon-feed"></i>' + name,
                action: function() {
                    callModuleFunction("trackworks.addSelectionToStream", sid);
                }
            });
        })($(this).attr("data-stream-id"), $(this).attr("data-name"));
    });

    return submenu;
}


function createMenu(e, m, dst) {

    var pageW = $(document).width();
    var pageH = $(document).height();
    var windH = $(window).height();

    leftSide = (e.pageX < pageW / 2);
    topSide = (e.clientY < windH / 2);

    $("div.rm_mouse_menu_wrap").remove();

    m.appendTo(dst);

    leftSide ? m.css("left", (e.pageX + 4) + "px") : m.css({"left": (e.pageX - 4 - m.get(0).scrollWidth) + "px"});
    topSide ? m.css("top", (e.pageY + 4) + "px") : m.css({"top": (e.pageY - 4 - m.get(0).scrollHeight) + "px"});

    return m;

}

function arrayToSubmenu(e, el)
{
    var pageW = $(document).width();
    var pageH = $(document).height();
    var windH = $(window).height();

    leftSide = (e.pageX < pageW / 2);
    topSide = (e.clientY < windH / 2);

    var m = $("<ul>").addClass("rm_mouse_menu");

    (leftSide === false) ? m.addClass("rm_menu_right") : null;
    (topSide === false) ? m.addClass("rm_menu_bottom") : null;

    el.forEach(function(item, i) {
        m.append(arrayToItem(e, item));
    });

    return m;
}

function arrayToItem(e, el)
{
    var subArrow = $("<i>").addClass("icon-arrow-right2 rm-right");
    var item = $("<li>");
    var span = $("<span>").html(el.name).addClass("rm_mouse_menu_title");

    item.addClass("rm_mouse_menu_item");

    if (el.name === "")
    {
        item.addClass("rm_mouse_menu_separator");
        item.append(span);
        return item;
    }
    
    if (el.enabled === false)
    {
        item.addClass("rm_mouse_menu_disabled");
        item.append(span);
        return item;
    }

    if (el.submenu !== undefined)
    {
        span.prepend(subArrow);
    }

    item.append(span);

    if (el.submenu !== undefined)
    {
        item.append(arrayToSubmenu(e, el.submenu));
        return item;
    }

    if (el.action !== undefined)
    {
        item.bind('click', el.action);
    }
    return item;
}

function hideTracklistMenu()
{
    $("div.rm_mouse_menu_wrap")
            .remove();
}