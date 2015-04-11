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

    module.directive("watchPlaying", ["NowPlayingWatcher", function (NowPlayingWatcher) {
        return {
            restrict: "A",
            scope: {
                channel: "=watchPlaying"
            },
            link: function (scope, element, attrs) {

                NowPlayingWatcher.register(element);

                element.on("$destroy", function () {
                    NowPlayingWatcher.unRegister(element);
                });

            }
        }
    }]);

    module.directive("npRefresh", ["$schedule", "$timeout", function ($schedule, $timeout) {
        return {
            restrict: "A",
            scope: {
                channel: "@npRefresh"
            },
            link: function (scope, element, attrs) {

                var operate = function () {
                        $timeout.cancel(timer);
                        var children = element.children().filter(function (elem) {
                                var top = $(window).scrollTop(),
                                    offset = $(this).offset(),
                                    height = $(this).outerHeight();
                                return offset.top + height > top && offset.top - top < $(window).height();
                            }),
                            channels = children.map(function () {
                                return $(this).scope()[scope.channel]
                            }).toArray(),
                            ids = channels.map(function (elem) {
                                return elem.sid
                            }).join(",");

                        if (ids == "") {
                            timer = $timeout(operate, 5000);
                            return;
                        }

                        $schedule.whatsOnChannels(ids).then(function (data) {
                            for (var i = 0, length = channels.length; i < length; i++) {
                                var id = channels[i].sid;
                                if (data[id] !== undefined) {
                                    channels[i].now_playing = (data[id].artist.length ? (data[id].artist + " - ") : "") + data[id].title;
                                    channels[i].listeners_count = data[id].listeners_count;
                                    channels[i].bookmarks_count = data[id].bookmarks_count;
                                } else {
                                    channels[i].now_playing = "";
                                }
                            }
                            timer = $timeout(operate, 10000);
                        }, function () {
                            timer = $timeout(operate, 10000);
                        });
                    },
                    timer = null;

                scope.$on("$destroy", function () {
                    $timeout.cancel(timer);
                });

                timer = $timeout(operate, 10000);

            }
        }
    }]);


})();
