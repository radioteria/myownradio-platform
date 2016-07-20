function showTagEditorBox(track_id)
{
    $.post("/radiomanager/api/getTrackItem", { 
        track_id : track_id, 
        type : "tags"
    }, function (json) 
    {
                json.tid = track_id;
                
            var item = $("#tagEditorTemplate").tmpl(json);
                item.find(".rm_mbox_btn_close").bind("click", function () { item.remove(); });
                item.find(".rm_mbox_close_btn").bind("click", function () { item.remove(); });
                item.find(".rm_mbox_btn_save").bind("click", function () { 
                    $.post("/radiomanager/changeTrackInfo", item.find('form').serialize(), function (json) {
                        try {
                            if(json.code !== "SUCCESS") {
                               myMessageBox(json.code);
                            } else {
                                showPopup("Track information successfully updated");
                                json.data.forEach(function(el) {
                                    if(typeof el.result[1] !== undefined) {
                                        callModuleFunction("tracklist.trackUpdate", el.value, el.result[1]);
                                    }
                                });
                            }
                        } catch(e) {

                        }
                        item.remove();
                    });
                });
            item.appendTo("body");
    });
}