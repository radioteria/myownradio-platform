/* Validator section */
(function(){
    
    $(".page-user-form").find("input[type=\"text\"], input[type=\"password\"]").live("change", function(){
        $(".page-form-input-status").css("opacity", 0).text("");
    });

    $.fn.extend({
        jsubmit: function(url) {
            
            $(this).live("submit", function(){
            
                $(".page-form-input-status").css("opacity", 0).text("");
                
                var args = $(this).serialize();
                var href = $(this).attr("action");
                            
                $.post(href, args + "&submit=true", function(json) {
                    if (json.status === 1)
                    {  
                        // Goto success file
                        if (typeof url !== "undefined") {
                            window.location.href = url;
                        }
                    }
                    else
                    {
                        // Displaying context
                        var context = json.context;
                        var message = json.message;
                        $(".page-form-input-status." + context).css("opacity", 1).text(message);
                    }
                });
            
                return false;

            });

        }
        
    });
})();