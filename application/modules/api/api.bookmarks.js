/**
 * Created by Roman on 06.04.15.
 */
(function () {
    var module = angular.module("application");

    module.directive("bookmarkIcon", [function () {
        return {
            scope: {
                bookmarkIcon: "="
            },
            restrict: "A",
            link: function (scope, element) {
                scope.$watch("bookmarkIcon.bookmarked", function (value) {
                    if (value == 1) {
                        element.removeClass("icon-heart-o");
                        element.addClass("icon-heart");
                    } else {
                        element.removeClass("icon-heart");
                        element.addClass("icon-heart-o");
                    }
                });
            }
        }
    }]);

    module.factory("$bookmarks", ["$api", function ($api) {
        return {
            addBookmark: function (channel) {
                return $api.put("/api/v2/bookmark", {
                    stream_id: channel.sid
                });
            },
            removeBookmark: function (channel) {
                return $api.delete("/api/v2/bookmark", {
                    stream_id: channel.sid
                });
            }
        }
    }])
})();
