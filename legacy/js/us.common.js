(function(){
    
    function documentResize()
    {
        $(".page-head-prefix").width($(".page-head-logo-cell").offset().left);
    }
    
    $(window).bind("resize", function(){
        documentResize();
    });
    
    $(document).on("ready", function(){
        documentResize();
        (function (token) {
            $.ajaxSetup({
                headers: { 'My-Own-Token': token }
            });
        })($("body").attr("token"));
    });
    
    $(".-fix-center").livequery(function(){
        var w = $(this).width();
        var h = $(this).height();
        $(this).width(w + w%2).height(h+h%2);
    });
    

    /* Ajax Page Load Section */
    $(document).on("scroll", function (e) {
        if($("body").hasClass("partial"))
        {
            var bottom = $(document).height() - $(window).height() - $(window).scrollTop();
            if(bottom < 400)
            {
                if($("body").hasClass("ajaxBusy") === false)
                {
                    ajaxGetContent();
                }
            }
        }
        
    });
    
    $(".page-body").livequery(function(){
        $("<div>").addClass("rm-page-busy").text("LOADING...").appendTo($(this));
    });

    function ajaxGetContent()
    {
        var from = $("._ajax-upload-subject").children().length;
        var url = window.location.href;
        $("body").addClass("ajaxBusy");
        if(url.indexOf("?") > -1)
        {
            url += "&start=" + from;
        }
        else
        {
            url += "?start=" + from;
        }
        $.get(url, function(data){
            var elements = $(data).find("._ajax-upload-subject").children();
            if(elements.length === 0)
            {
                $("body").removeClass("partial");
            }
            else
            {
                elements.appendTo("._ajax-upload-subject");
            }
            $("body").removeClass("ajaxBusy");
        });
    }
    /* End of Ajax Page Load Section */
    
})();


