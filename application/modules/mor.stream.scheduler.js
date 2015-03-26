/**
 * Created by Roman on 05.03.2015.
 */

(function () {

    var DEFAULT_INTERVAL = 5000,

        scheduler;

    scheduler = angular.module("mor.stream.scheduler", ["Site"]);

    scheduler.run(["$rootScope", function ($rootScope) {
        $rootScope.callOrSet = function (key, value, context) {
            if (angular.isUndefined(context[key])) {
                return false;
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
                "scheduler.rest",
                function ($scope, $timeout, rest) {
                    var delay,
                        prevUniqueId,
                        update = function () {

                            $timeout.cancel(delay);

                            if ($scope.ngModel.sid === undefined) {
                                return
                            }

                            rest.getNowPlaying($scope.ngModel).onSuccess(
                                function (response) {
                                    $scope.$root.callOrSet("onInterval", response, $scope);
                                    if (prevUniqueId !== response.current.unique_id) {
                                        prevUniqueId = response.current.unique_id;
                                        $scope.$root.callOrSet("onTrackChange", response.current, $scope);
                                    }
                                    var end = response.current.duration + response.current.time_offset - response.position;
                                    delay = $timeout(update, Math.min(DEFAULT_INTERVAL, end))
                                }, function () {
                                    $scope.$root.callOrSet("onInterval", undefined, $scope);
                                    if (prevUniqueId !== undefined) {
                                        prevUniqueId = undefined;
                                        $scope.$root.callOrSet("onTrackChange", undefined, $scope);
                                    }
                                    delay = $timeout(update, DEFAULT_INTERVAL)
                                }
                            )
                        },
                        stop = function () {

                            $timeout.cancel(delay);

                            if (prevUniqueId !== undefined) {
                                $scope.$root.callOrSet("onTrackChange", undefined, $scope);
                                prevUniqueId = undefined;
                            }

                            $scope.$root.callOrSet("onInterval", undefined, $scope);

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
            link: function ($scope, $element, $attributes) {
                //$scope.onInterval = $compile($attributes.onInterval)($scope);
                //$scope.onTrackChange = $compile($attributes.onTrackChange)($scope);
                //console.log($scope.onInterval);
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
                                return false;
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