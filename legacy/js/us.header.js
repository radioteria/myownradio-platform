(function(){
    $(".page-head-form-search > input[type='text']").livequery(function(){
        this.setSelectionRange($(this).val().length, $(this).val().length);
    });
})();