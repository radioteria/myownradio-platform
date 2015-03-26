/**
 * Created by Roman on 09.02.2015.
 */

(function () {

    var test = new ActiveArray();

    var bindListEvents = function () {

        var selector = ".test-list";

        test.onAdd(function (element) {
            $(selector).append($("<li>").html(element));
        });

        test.onRemove(function (index) {
            $(selector).children().eq(index).remove();
        });

        test.onClear(function () {
            $(selector).children().remove();
        });

        test.restore(selector, function (elem, data) {
            $(selector).empty();
            data.forEach(function (element) {
                $(selector).append($("<li>").html(element));
            });
        });

    };

    var bind = test.onAdd(function (element) {
        console.log("Added element", element);
    });

    $(document).ready(function () {
        bindListEvents();
    });


    test.add("Element 1");
    test.add("Element 2");

    bind.unbind();

    test.add("Element 3");
    test.add("Element 4");

    test.onAdd(function (element) {
        console.log("Another element", element);
    });

    test.add("Element 5");
    test.add("Element 6");


})();