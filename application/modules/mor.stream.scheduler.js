/**
 * Created by Roman on 05.03.2015.
 */

(function () {

    var DEFAULT_INTERVAL = 10000;
    var scheduler = angular.module("mor.stream.scheduler", ["Site"]);

    scheduler.run(["$rootScope", function ($rootScope) {
        $rootScope.callOrSet = function (key, value, context) {
            if (angular.isUndefined(context[key])) {
                return;
            }
            $rootScope.$applyAsync(function () {
                if (typeof context[key] === "function") {
                    context[key].call(this, value);
                } else {
                    context[key] = value;
                }
            });
        }
    }]);

    scheduler.factory("scheduler.rest", ["$http", "Response", function ($http, Response) {
        return {
            getNowPlaying: function (stream) {
                var action = $http({
                    method: "GET",
                    ignoreLoadingBar: true,
                    url: "/api/v2/streams/getNowPlaying",
                    params: {
                        stream_id: stream.sid
                    }
                });
                return Response(action);
            },
            getSchedule: function (stream) {
                var action = $http({
                    method: "GET",
                    ignoreLoadingBar: true,
                    url: "/api/v2/streams/getSchedule",
                    params: {
                        stream_id: stream.sid
                    }
                });
                return Response(action);
            }
        }
    }]);

    scheduler.directive("now", [function () {
        return {
            require: "ngModel",
            restrict: "AE",
            scope: {
                ngModel: "=",
                onInterval: "=",
                onTrackChange: "="
            },
            controller: [
                "$scope",
                "$timeout",
                "$schedule",
                function ($scope, $timeout, $schedule) {
                    var delay,
                        prevUniqueId,
                        update = function () {

                            $timeout.cancel(delay);

                            if (!angular.isObject($scope.ngModel)) {
                                return
                            }

                            $schedule.nowPlaying($scope.ngModel).then(function (response) {
                                $scope.$root.callOrSet("onInterval", response, $scope);
                                if (prevUniqueId !== response.unique_id) {
                                    prevUniqueId = response.unique_id;
                                    $scope.$root.callOrSet("onTrackChange", response, $scope);
                                }
                                var end = response.duration + response.time_offset - response.position;
                                delay = $timeout(update, Math.min(DEFAULT_INTERVAL, end))
                            }, function () {
                                $scope.$root.callOrSet("onInterval", null, $scope);
                                if (prevUniqueId !== null) {
                                    prevUniqueId = null;
                                    $scope.$root.callOrSet("onTrackChange", null, $scope);
                                }
                                delay = $timeout(update, DEFAULT_INTERVAL)
                            });

                        },
                        stop = function () {

                            $timeout.cancel(delay);

                            if (prevUniqueId !== null) {
                                $scope.$root.callOrSet("onTrackChange", null, $scope);
                                prevUniqueId = null;
                            }

                            $scope.$root.callOrSet("onInterval", null, $scope);

                        };

                    $scope.$watch("ngModel", function () {

                        (($scope.ngModel && $scope.ngModel.sid) ? update : stop)();

                    });

                    $scope.$on("$destroy", function () {

                        $timeout.cancel(delay)

                    });

                }
            ]
        }
    }]);

    scheduler.directive("schedule", [function () {
        return {
            require: "ngModel",
            restrict: "AE",
            scope: {
                ngModel: "=",
                onInterval: "=",
                onTrackUpdate: "="
            },
            controller: [
                "$scope",
                "$timeout",
                "scheduler.rest",
                function ($scope, $timeout, rest) {
                    var delay,
                        previousUniqueId,
                        update = function () {
                            if (angular.isUndefined($scope.ngModel.sid)) {
                                return;
                            }

                            rest.getSchedule($scope.ngModel).onSuccess(
                                function (response) {
                                    var currentTrack = response.tracks[response.current];
                                    $scope.$root.callOrSet("onInterval", response, $scope);

                                    if (previousUniqueId != currentTrack.unique_id) {
                                        $scope.$root.callOrSet("onTrackChange", response, $scope);
                                        previousUniqueId = currentTrack.unique_id;
                                    }

                                    var end = currentTrack.duration + currentTrack.time_offset - response.position;
                                    delay = $timeout(update, Math.min(DEFAULT_INTERVAL, end))
                                }, function () {
                                    $scope.$root.callOrSet("onInterval", null, $scope);

                                    if (previousUniqueId != null) {
                                        $scope.$root.callOrSet("onTrackChange", null, $scope);
                                        previousUniqueId = null;
                                    }

                                    delay = $timeout(update, DEFAULT_INTERVAL)
                                }
                            )
                        },
                        stop = function () {
                            $timeout.cancel(delay);
                            if (previousUniqueId != null) {
                                $scope.$root.callOrSet("onTrackChange", null, $scope);
                                previousUniqueId = null;
                            }
                            $scope.$root.callOrSet("onInterval", null, $scope);
                        };

                    $scope.$watch("ngModel", function (value) {

                        (value ? update : stop)();

                    });

                    $scope.$on("$destroy", function () {

                        $timeout.cancel(delay)

                    });

                }
            ]
        }
    }]);

})();