
(function () {

    var search = angular.module("Search", ['Site']);

    search.directive("navigableList", ["$document", function ($document) {
        return {
            link: function ($scope, $element, $attributes) {
                var SELECTED_CLASS = "selected",
                    bindHandle = function (event) {
                        if (event.which == 40) { // down
                            next();
                            event.stopPropagation();
                            return false;
                        } else if (event.which == 38) { // up
                            prev();
                            event.stopPropagation();
                            return false;
                        } else if (event.which == 13) {
                            $element.children("." + SELECTED_CLASS).trigger("click");
                            event.stopPropagation();
                            return false;
                        }
                    },
                    handle = $document.bind("keydown", bindHandle);

                $element.on('$destroy', function () {
                    handle.unbind("keydown", bindHandle);
                });

                $element.bind("mouseover", function (event) {
                    if ($element.children(event.target).length > 0) {
                        var li = $(event.target).parents("li");
                        select(li.index());
                    }
                });

                var next = function () {
                    var prevElem = $element.children("." + SELECTED_CLASS);
                    if (prevElem.length == 0) {
                        $element.children().eq(0).addClass(SELECTED_CLASS);
                    } else if (prevElem.next().length > 0) {
                        prevElem.removeClass(SELECTED_CLASS);
                        prevElem.next().addClass(SELECTED_CLASS);
                    }
                };

                var prev = function () {
                    var nextElem = $element.children("." + SELECTED_CLASS);
                    if (nextElem.prev().length > 0) {
                        nextElem.removeClass(SELECTED_CLASS);
                        nextElem.prev().addClass(SELECTED_CLASS);
                    }
                };

                var select = function (index) {
                    $element.children("." + SELECTED_CLASS).removeClass(SELECTED_CLASS);
                    $element.children().eq(index).addClass(SELECTED_CLASS);
                };

                select(0);
            }
        }
    }]);

})();