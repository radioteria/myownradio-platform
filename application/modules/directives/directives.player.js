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

    module.factory("NowPlayingWatcher", ["$timeout", "$document", "$window", "$schedule",

        function ($timeout, $document, $window, $schedule) {

            var channels = [],
                cache = {},
                operate = function () {
                    var selection = [];
                    for (var i = 0, length = channels.length; i < length; i ++) {
                        if (channels[i].offset().top + channels[i].outerHeight() > $document.scrollTop() && channels[i].offset().top - $document.scrollTop() < $window.innerHeight) {
                            selection.push(channels[i].scope().channel);
                        }
                    }
                    var ids = selection.map(function (elem) { return elem.sid }).join(",");
                    $schedule.whatsOnChannels(ids).then(function (data) {
                        for (var i = 0, length = selection.length; i < length; i ++) {
                            if (data[selection[i].sid] !== undefined) {
                                cache[selection[i].sid] = (data[selection[i].sid].artist.length ? (data[selection[i].sid].artist + " - ") : "") + data[selection[i].sid].title;
                                selection[i].now_playing = cache[selection[i].sid];
                            }
                        }
                        $timeout(operate, 5000);
                    });
                };

            $timeout(operate, 1000);

            return {
                register: function (element) {
                    if (channels.indexOf(element) == -1) {
                        var id = element.scope().channel.sid;
                        channels.push(element);
                        if (cache[id] !== undefined) {
                            element.scope().channel.now_playing = cache[id];
                        }
                    }
                },
                unRegister: function (element) {
                    var offset;
                    if (offset = channels.indexOf(element) != -1) {
                        channels.splice(offset, 1);
                    }
                }
            }

        }

    ]);


})();
