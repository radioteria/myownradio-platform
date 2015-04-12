(function(){
    var timerHandle = false
    
    $("#filterBox").livequery(function(){
        if($("body").hasClass("library", "unused"))
        {
            $(this).bind('textchange', function(){
                if($(this).val().length > 0)
                {
                    $("#filterReset").addClass("visible");
                }
                else
                {
                    $("#filterReset").removeClass("visible");
                }
                queryTrackFilter();
            });
        }
    });  
    
    $("#filterReset").live("click", function(e){
        $("#filterBox").val("");
        $("#filterReset").removeClass("visible");
        queryTrackFilter();
    });
    
    function queryTrackFilter() 
    {
        if(timerHandle)
        {
            window.clearTimeout(timerHandle);
        }
        timerHandle = window.setTimeout(function(){
            timerHandle = false;
            ajaxGetTrackUniversal(true);
        }, 200);
    }
})();
