(function(){
    $("img.page-stream-list-item-cover").livequery(function(){

        $(this).load(function(){
            $(this).css("opacity", 1);
        }).each(function() {
            if(this.complete) $(this).load();
        });

            
    });
    
    $(".page-stream-list-list > li").livequery(function(){
        $(this).css({opacity:1});
    });
})();