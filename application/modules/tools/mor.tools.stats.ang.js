(function () {

    var tools = angular.module("mor.tools");

    tools.constant("STATS_INTERVAL", 10000);

    tools.run(["$timeout", "$rootScope", "StatsFactory", "STATS_INTERVAL", "$localize",

        function ($timeout, $rootScope, StatsFactory, STATS_INTERVAL, $localize) {

            $rootScope.stats = {};

            var rotate = function () {
                StatsFactory.getActiveListeners().onSuccess(function (data) {
                    $rootScope.stats.listeners_count = data;
                });
                $timeout(rotate, STATS_INTERVAL);
            };

            rotate();

            $rootScope.tr = function (key, context) {
                return $localize.analyze(locale[key], context);
            };

            $rootScope.pl = function (key, count, context, offset) {
                return $localize.pluralize(locale[key], count, context, offset);
            }

        }


    ]);

    tools.directive("morProgress", [function () {
        return {
            restrict: "C",
            template: '<div class="progress-background"><div class="progress-handle"></div></div>',
            scope: {
                progressValue: "=",
                progressMax: "="
            },
            requires: "?progressValue, ?progressMax",
            link: function (scope, element, attrs) {
                var value,
                    max,
                    apply = function () {
                        if (!max || !value || value > max) {
                            el.css("width", 0);
                        } else {
                            el.css("width", (100 / max * value) + "%");
                        }
                    },
                    el = element.find(".progress-handle");
                scope.$watch("progressValue", function (v) {
                    value = v;
                    apply();
                });
                scope.$watch("progressMax", function (v) {
                    max = v;
                    apply();
                });
            }
        }
    }]);

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

        function grabObject(obj, path, def) {
            var i, keys, accumulator;
            if (!angular.isString(path)) {
                throw new Error("'path' must be a 'string'!");
            }
            if (path.length == 0) {
                return def;
            }
            keys = path.split(".");
            accumulator = obj;
            for (i = 0; i < keys.length; i++) {
                if (!angular.isObject(accumulator)) {
                    return "";
                }
                accumulator = accumulator[keys[i]];
            }
            return accumulator;
        }

        function compareEnds(needle, subject) {
            var subj = subject.toString();
            if (subj.length < needle.length) {
                return false;
            }
            return subj.slice(-needle.length) == needle;
        }

        var locale = {
            analyze: function (input, context, count) {
                if (angular.isArray(input)) {
                    var i, arr = [];
                    for (i = 0; i < input.length; i++) {
                        arr.push(locale.analyze(input[i], context, count));
                    }
                    return arr;
                } else if (angular.isObject(input)) {
                    var key, obj = {};
                    for (key in input) if (input.hasOwnProperty(key)) {
                        obj[key] = locale.analyze(input[key], context, count);
                    }
                    return obj;
                } else if (angular.isString(input)) {
                    return input.replace(/\{\{\s*([a-z0-9\\_\.]*)\s*\}\}/g, function (k1, k2) {
                        return htmlEscape(grabObject(context, k2, count));
                    });
                } else if (angular.isNumber(input)) {
                    return input;
                } else {
                    return "???";
                }
            },
            pluralize: function (when, count, context, offset) {
                var key;
                offset = offset || 0;
                if (!angular.isObject(when)) {
                    throw new Error("'when' must be an object!");
                }
                if (!angular.isNumber(count)) {
                    throw new Error("'count' must be a number!");
                }
                for (key in when) if (when.hasOwnProperty(key)) {
                    if (angular.isNumber(count) && count == parseInt(key)) {
                        return locale.analyze(when[key], context, count - offset);
                    } else if (angular.isNumber(count) && key.slice(0, 1) == "*" && compareEnds(key.slice(1), count.toString())) {
                        return locale.analyze(when[key], context, count - offset);
                    } else if (key == "other") {
                        return locale.analyze(when[key], context, count - offset);
                    }
                }
                return "";
            }
        };
        return locale;
    }]);

    tools.directive("translate", ["$localize", "$filter", function ($localize, $filter) {
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
                                var translated = $localize.analyze(locale[label], scope.args);
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


    tools.directive("pluralize", ["$localize", "$filter", function ($localize, $filter) {
        return {
            scope: {
                count: "=",
                when: "@",
                args: "="
            },
            restrict: "E",
            compile: function () {
                return {
                    pre: function (scope, element, attr) {

                        var translate = function () {
                            var translated = $localize.pluralize(locale[scope.when], scope.count, scope.args);
                            if (angular.isDefined(attr["filter"])) {
                                var filter = $filter(attr['filter']);
                                translated = filter(translated);
                            }
                            element.html(translated);
                        };

                        scope.$watchGroup(["args", "count"], function () {
                            translate();
                        });

                    }
                }
            }
        }
    }]);

})();