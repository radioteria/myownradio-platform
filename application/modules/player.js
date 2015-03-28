/**
 * Created by roman on 31.12.14.
 */

(function () {
    var player = angular.module("RadioPlayer", ['Site']);

    player.run(["$rootScope", "$http", "Response", "Streams", "$timeout", "$location", "Popup", "$analytics", "TrackPreviewService",

        function ($rootScope, $http, Response, Streams, $timeout, $location, Popup, $analytics, TrackPreviewService) {

            var handle = false;

            $rootScope.player = {
                isPlaying: false,
                isLoaded: false,
                isBuffering: false,
                nowPlaying: null,
                currentID: null,
                currentStream: null,
                url: null,
                page: undefined,
                visible: true,
                goCurrent: function () {
                    $location.url($rootScope.player.page);
                },
                controls: {
                    reload: function () {
                        var $stream = $rootScope.player.currentStream;
                        if ($rootScope.player.isPlaying === true) {
                            console.log("Reload", $stream.sid, $rootScope.defaults.format);
                            $rootScope.player.url = "http://myownradio.biz:7778/audio?s=" + $stream.sid + "&f=" + $rootScope.defaults.format;
                            $rootScope.player.controls.play();
                        }
                    },
                    loadStream: function ($stream) {
                        $rootScope.player.url = "/flow?s=" + $stream.sid + "&f=" + $rootScope.defaults.format;
                        $rootScope.player.currentID = $stream.sid;
                        $rootScope.player.controls.play();
                        $rootScope.player.currentStream = $stream;
                        $rootScope.player.page = "/streams/" + $stream.key;
                        $rootScope.player.isLoaded = true;
                    },
                    play: function () {

                        $analytics.eventTrack('Play', { category: 'Streams', label: $rootScope.player.currentID });

                        $rootScope.player.isBuffering = true;
                        realPlayer.play($rootScope.player.url);
                        $rootScope.player.isPlaying = true;

                        TrackPreviewService.stop();

                    },
                    stop: function () {
                        realPlayer.stop();
                        $timeout.cancel(handle);
                        $rootScope.player.isBuffering = false;
                        $rootScope.player.nowPlaying = null;
                        $rootScope.player.isPlaying = false;
                    },
                    switch: function () {
                        $rootScope.player.isPlaying ?
                            $rootScope.player.controls.stop() :
                            $rootScope.player.controls.play();
                    },
                    playSwitchStream: function ($stream) {
                        if ($rootScope.player.currentID == $stream.sid) {
                            $rootScope.player.controls.switch();
                        } else {
                            $rootScope.player.controls.stop();
                            $rootScope.player.controls.loadStream($stream);
                        }
                    },
                    unload: function () {
                        $rootScope.player.controls.stop();
                        $rootScope.player.currentID = null;
                        $rootScope.player.currentStream = null;
                        $rootScope.player.page = null;
                        $rootScope.player.isLoaded = false;
                    }
                }
            };

            $rootScope.$watch("player.nowPlaying.unique_id", function (newValue) {
                if (newValue && $rootScope.player.isPlaying) {
                    Popup.message("<b>" + htmlEscape($rootScope.player.nowPlaying.caption) + "</b><br>now on <b>" + htmlEscape($rootScope.player.currentStream.name) + "</b>", 5000);
                }
            });

            var realHandle = null;
            var realPlayer = {
                play: function (url, onPlay) {

                    onPlay = onPlay || function () {
                    };

                    realPlayer.stop();
                    realHandle = new Audio5js({
                        swf_path: "/swf/audio5js.swf",
                        codecs: ['mp3'],
                        ready: function () {
                            this.on("timeupdate", function () {
                                if ($rootScope.player.isBuffering == true) {
                                    $rootScope.player.isBuffering = false;
                                    $rootScope.$digest();
                                }

                            });
                            this.on("error", function () {
                                $rootScope.player.isBuffering = true;
                                $timeout(function () {
                                    realPlayer.play(url)
                                }, 1000);
                            });

                            this.load(url);
                            this.play();
                        }
                    });
                },
                stop: function () {
                    if (realHandle instanceof Audio5js) {
                        realHandle.destroy();
                    }
                    realHandle = null;
                }
            };

        }

    ]);

    player.directive("play", [function () {
        return {
            scope: {
                obj: "="
            },
            template: '<div class="play-pause"><div class="toggle" ng-click="playRadio(obj)" mor-tooltip="Play/Stop">\
                            <i ng-show="player.isPlaying && player.currentID == obj.sid" class="icon-stop"></i>\
                            <i ng-hide="player.isPlaying && player.currentID == obj.sid" class="icon-play-arrow"></i>\
                            </div></div>',
            controller: ["$scope", "$rootScope", function ($scope, $rootScope) {
                $scope.playRadio = function ($stream) {
                    $rootScope.player.controls.playSwitchStream($stream);
                };
                $scope.player = $rootScope.player;
            }]
        }
    }]);

    player.directive("preview", ["TrackPreviewService", function (TrackPreviewService) {
        return {
            template: '<span class="only-first-element" mor-tooltip="Click to preview track">' +
                '<i ng-if="!isPlaying" class="icon-play-circle-fill"></i>' +
                '<i ng-if="isPlaying" class="icon-pause-circle-fill"></i>' +
                '</span>',
            restrict: "E",
            require: "ngModel",
            scope: {
                ngModel: "="
            },
            link: function ($scope, $element, $attrs) {
                $scope.isPlaying = false;
                $element.on("mousedown", function (event) {
                    event.preventDefault();
                    event.stopPropagation();
                    TrackPreviewService.play($scope.ngModel);
                    $scope.ngModel.is_new = 0;
                });
                $scope.$on("preview.start", function (event, track) {
                    if ($scope.ngModel == null) return;
                    if (track.tid == $scope.ngModel.tid) {
                        $scope.isPlaying = true;
                        $scope.$applyAsync();
                    }
                });
                $scope.$on("preview.stop", function (event, track) {
                    $scope.isPlaying = false;
                    $scope.$applyAsync();
                });
                TrackPreviewService.ifSomethingIsPlaying(function () {
                    if ($scope.ngModel == null) return;
                    if (this.tid == $scope.ngModel.tid) {
                        $scope.isPlaying = true;
                    }
                });
            }
        };
    }]);

    player.factory("TrackPreviewService", ["$rootScope", "Popup",

        function ($rootScope, Popup) {

            var jPlayer = $("<div></div>").appendTo("body").jPlayer({
                swfPath: "jplayer",
                supplied: "mp3",
                play: function (event) {
                    //Popup.message("Preview of <b>" + htmlEscape(currentTrack.artist + " - " + currentTrack.title) + "</b> is started");
                    $rootScope.player.controls.stop();
                    $rootScope.$broadcast("preview.start", currentTrack);
                },
                ended: function (event) {
                    //Popup.message("Preview of <b>" + htmlEscape(currentTrack.artist + " - " + currentTrack.title) + "</b> is finished");
                    $rootScope.$broadcast("preview.stop");
                    currentTrack = null;
                },
                error: function (event) {
                    Popup.message("Error: " + htmlEscape(event.jPlayer.error.message));
                    $rootScope.$broadcast("preview.stop");
                    currentTrack = null;
                },
                solution: "html, flash",
                volume: 1,
                wmode: 'window'
            });

            var currentTrack = null;

            var service = {
                play: function (object) {
                    if (currentTrack != null && currentTrack.tid == object.tid) {
                        service.stop();
                    } else {
                        service.stop();
                        jPlayer.jPlayer("setMedia", { mp3: "/content/audio/".concat(object.tid) });
                        jPlayer.jPlayer("play");
                        currentTrack = object;
                    }
                },
                stop: function () {
                    jPlayer.jPlayer("clearMedia");
                    $rootScope.$broadcast("preview.stop");
                    currentTrack = null;
                },
                ifSomethingIsPlaying: function (callback) {
                    if (currentTrack != null)
                        callback.call(currentTrack);
                }
            };

            return service;

        }

    ]);

})();

