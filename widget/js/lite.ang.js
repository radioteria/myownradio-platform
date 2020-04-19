(function () {
    var empty = angular.module("Site", []);
    var m = angular.module("application", ["mor.stream.scheduler", "WidgetPlayer"]);
    m.config(["$locationProvider", function ($locationProvider) {
        $locationProvider.html5Mode(true);
    }]);
    m.controller("WidgetPlayerController", ["$streams", "$scope", "$location",
        function ($streams, $scope, $location) {
            $streams.getStream($location.search().stream_id).onSuccess(function (data) {
                $scope.streamObject = data;
                if ($location.search().auto == 1) {
                    $scope.player.controls.loadStream(data);
                }
            }, function () {
                $scope.streamExists = false;
            });
        }
    ]);
    m.factory("$streams", [ "Response", "$http",
        function (Response, $http) {
            return {
                getStream: function (streamId) {
                    return Response($http({
                        method: "GET",
                        url: "/api/v2/streams/getOne",
                        params: {
                            stream_id: streamId
                        }
                    }));
                }
            }
        }
    ]);
    m.directive("dBackgroundColor", [
        function () {
            return {
                scope: {
                    dBackgroundColor: "="
                },
                link: function ($scope, $element, $attributes) {
                    $scope.$watch("dBackgroundColor", function (newColor) {
                        if (angular.isDefined(newColor)) {
                            $element.css("background-color", newColor);
                        } else {
                            $element.css("background-color", "");
                        }
                    });
                }
            }
        }
    ]);
    m.directive("dBackgroundImage", [
        function () {
            return {
                scope: {
                    dBackgroundImage: "@"
                },
                link: function ($scope, $element, $attrs) {
                    $scope.$watch("dBackgroundImage", function (newUrl) {
                        $element.css({opacity: 0});
                        if (angular.isDefined(newUrl)) {
                            angular.element("<img>").on('load', function () {
                                $element.css("background-image", "url(" + newUrl + ")");
                                $element.animate({opacity: 1}, 500);
                            }).attr("src", newUrl);
                        } else {
                            $element.css("background-image", "");
                        }
                    });
                }
            }
        }
    ]);
    m.factory("Response", function () {
        return function (promise) {
            return {
                onSuccess: function (onSuccess, onError) {
                    onSuccess = onSuccess || function () {
                    };
                    onError = onError || function () {
                    };
                    promise.then(function (res) {
                        var response = res.data;
                        if (response.code == 1) {
                            onSuccess(response.data, response.message);
                        } else {
                            onError(response.message);
                        }
                    });
                }
            }
        }
    });

    angular.module("WidgetPlayer", [])
        .run(["$rootScope", function ($rootScope) {
            $rootScope.player = {
                isPlaying: false,
                isLoaded: false,
                isBuffering: false,
                nowPlaying: null,
                currentID: null,
                currentStream: null,
                url: null,
                goCurrent: function () {
                    $location.url($rootScope.player.page);
                },
                controls: {
                    loadStream: function ($stream) {
                        $rootScope.player.url = "/flow?s=" + $stream.sid;
                        $rootScope.player.currentID = $stream.sid;
                        $rootScope.player.controls.play();
                        $rootScope.player.currentStream = $stream;
                        $rootScope.player.page = "/streams/" + $stream.key;
                        $rootScope.player.isLoaded = true;
                    },
                    play: function () {
                        $rootScope.player.isBuffering = true;
                        realPlayer.play($rootScope.player.url);
                        $rootScope.player.isPlaying = true;
                    },
                    stop: function () {
                        realPlayer.stop();
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

        }]);
})();