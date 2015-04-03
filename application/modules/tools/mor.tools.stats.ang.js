(function () {

    var tools = angular.module("mor.tools");

    tools.constant("STATS_INTERVAL", 10000);

    tools.run(["$timeout", "$rootScope", "StatsFactory", "STATS_INTERVAL", "$tr", "$localize",

        function ($timeout, $rootScope, StatsFactory, STATS_INTERVAL, $tr, $localize) {

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
            };

            $rootScope.pl = function (key, count, args) {
                return $localize.get(key, count, args);
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

    tools.factory("$localize", [function () {
        var locale = {
            get: function (key, count, args) {
                var input = window.locale[key];
                if (angular.isString(input)) {
                    return locale.analyze(input, count);
                } else if (angular.isObject(input) && !angular.isArray(input)) {
                    return locale.pluralize(input, count, args);
                } else {
                    return key;
                }
            },
            analyze: function (input, object, args) {
                var grabObject = function (obj, path, args) {

                    if (!angular.isString(path)) {
                        throw new Error("'path' must be a 'string'!");
                    }
                    if (path.length == 0) return input;

                    var i, keys = path.split("."), accumulator = obj;
                    for (i = 0; i < keys.length; i ++) {
                        if (i == 0 && keys[i] == "args") {
                            accumulator = args;
                            continue;
                        }
                        if (!angular.isObject(accumulator)) {
                            return "";
                        }
                        accumulator = accumulator[keys[i]];
                    }
                    return accumulator;
                };

                if (angular.isArray(input)) {
                    var i, arr = [];
                    for (i = 0; i < input.length; i++) {
                        arr.push(locale.analyze(input[i], object));
                    }
                    return arr;
                } else if (angular.isObject(input)) {
                    var key, obj = {};
                    for (key in input) if (input.hasOwnProperty(key)) {
                        obj[key] = locale.analyze(input[key], object);
                    }
                    return obj;
                } else if (angular.isString(input)) {
                    var a = input.replace(/(%[a-z0-9\\_\\.]+%)/g, function (match) {
                        var key = match.substr(1, match.length - 2);
                        return htmlEscape(grabObject(object, key, args));
                    });
                    a = a.replace(/(%%)/g, function (match) {
                        return htmlEscape(object);
                    });
                    return a;
                } else if (angular.isNumber(input)) {
                    return input;
                } else {
                    return "TR_UNKNOWN_OBJECT";
                }
            },
            pluralize: function (when, count, args) {
                var key;
                if (!angular.isObject(when)) {
                    throw new Error("'when' must be an 'object'!");
                }
                if (!angular.isArray(count) && !angular.isNumber(count)) {
                    throw new Error("'array' must be an 'array'!");
                }
                for (key in when) if (when.hasOwnProperty(key)) {
                    if (angular.isArray(count) && count.length == parseInt(key)) {
                        return locale.analyze(when[key], count, args);
                    } else if (angular.isNumber(count) && count == parseInt(key)) {
                        return locale.analyze(when[key], count, args);
                    } else if (angular.isArray(count) && key.slice(0, 1) == "*" && count.length.toString(10).slice(-1) == key.slice(-1)) {
                        return locale.analyze(when[key], count, args);
                    } else if (angular.isNumber(count) && key.slice(0, 1) == "*" && count.toString(10).slice(-1) == key.slice(-1)) {
                        return locale.analyze(when[key], count, args);
                    } else if (key == "other") {
                        return locale.analyze(when[key], count, args);
                    }
                }
                return "";
            }
        };
        return locale;
    }]);

    tools.factory("$tr", ["$localize", function ($localize) {
        return function ($key, args) {
            return $localize.get($key, args);
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