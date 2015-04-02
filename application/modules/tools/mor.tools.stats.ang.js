(function () {

    var tools = angular.module("mor.tools");

    tools.constant("STATS_INTERVAL", 10000);

    tools.run(["$timeout", "$rootScope", "StatsFactory", "STATS_INTERVAL", "$tr",

        function ($timeout, $rootScope, StatsFactory, STATS_INTERVAL, $tr) {

            $rootScope.stats = {};

            var rotate = function () {
                StatsFactory.getActiveListeners().onSuccess(function (data) {
                    $rootScope.stats.listeners_count = data;
                });
                $timeout(rotate, STATS_INTERVAL);
            };

            rotate();

            $rootScope.tr = function (key, args) {
                return $tr(key, args);
            }

        }


    ]);

    tools.factory("StatsFactory", ["$http", "Response", function ($http, Response) {
        return {
            getActiveListeners: function () {
                return Response($http.get("/api/v2/stats/listeners", {
                    ignoreLoadingBar: true
                }))
            }
        }
    }]);

    tools.factory("$tr", [function () {
        return function ($key, args) {

            var result;

            if (typeof locale[$key] == "undefined") {
                return $key;
            }

            result = locale[$key].replace(/(%[a-z0-9\\_]+%)/g, function (match) {
                var key = match.substr(1, match.length - 2);
                if (typeof args != "undefined" && typeof args[key] != "undefined") {
                    return htmlEscape(args[key]);
                } else {
                    return "";
                }
            });

            if (result.substr(0, 1) == "{" && result.substr(result.length - 1, 1) == "}") {

                result = JSON.parse(result);
            }

            return result;

        }
    }]);

    tools.directive("translate", ["$tr", "$filter", function ($tr, $filter) {
        return {
            scope: {
                args: "="
            },
            restrict: "E",
            compile: function () {
                return {
                    pre: function (scope, element, attr) {
                        var label = element.text(),
                            translate = function () {
                                var translated = $tr(label, scope.args);
                                if (angular.isDefined(attr["filter"])) {
                                    var filter = $filter(attr['filter']);
                                    translated = filter(translated);
                                }
                                element.html(translated);
                            };

                        if (angular.isDefined(scope.args)) {
                            scope.$watch("args", function () {
                                translate();
                            });
                        } else {
                            translate();
                        }

                    }
                }
            }
        }
    }]);

})();