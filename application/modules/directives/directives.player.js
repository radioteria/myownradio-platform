/**
 * Created by Roman on 07.04.15.
 */
(function () {
    var module = angular.module("application");

    module.directive("playIcon", ["$rootScope", function ($rootScope) {
        return {
            restrict: "A",
            scope: {
                playIcon: "="
            },
            link: function (scope, element, attrs) {
                $rootScope.$watchCollection("player", function (player) {
                    if (player.isPlaying && player.currentStream.sid == scope.playIcon.sid) {
                        element.removeClass("icon-play-arrow");
                        element.addClass("icon-stop");
                    } else {
                        element.removeClass("icon-stop");
                        element.addClass("icon-play-arrow");
                    }
                });
            }
        }
    }]);
})();
