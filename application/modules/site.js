/**
 * Created by roman on 31.12.14.
 */
(function () {

    var site = angular.module("Site", []);


    site.filter("streamsCatalog", [function () {
        return function (key) {
            return "/streams/" + key;
        }
    }]);

    site.filter("msToTime", [function () {
        return function (ms) {
            var seconds = parseInt(ms / 1000);
            var out_hours = parseInt(seconds / 3600);
            var out_minutes = parseInt(seconds / 60) % 60;
            var out_seconds = seconds % 60;
            return (out_hours ? (out_hours.toString() + ":") : "") + (out_minutes < 10 ? "0" : "")
                + out_minutes.toString() + ":" + (out_seconds < 10 ? "0" : "") + out_seconds.toString();
        }
    }]);

    site.filter("msToSmallTime", [function () {
        return function (ms) {
            var sign = (ms < 0 ? "-" : "+");
            var abs = Math.abs(ms);
            if (abs < 60000) {
                return sign + Math.floor(abs / 1000) + "s";
            } else {
                return sign + Math.floor(abs / 60000) + "m";
            }
        }
    }]);

    site.filter("humanTime", ["$rootScope", function ($rootScope) {

        return function (ms) {

            var totalSeconds = parseInt(Math.abs(ms / 1000));
            var days = parseInt(totalSeconds / 86400);
            var hours = parseInt(totalSeconds / 3600) % 24;
            var minutes = parseInt(totalSeconds / 60) % 60;

            if (ms < 0) {
                return $rootScope.tr("TR_HUMAN_TIME_OVERUSED", { days:days, hours:hours, minutes:minutes });
            } else {
                return $rootScope.tr("TR_HUMAN_TIME_FORMAT", { days:days, hours:hours, minutes:minutes });
            }

        }

    }]);

    site.filter("lighten", [function () {
        return function (color, rate) {
            var ex = new RegExp("^\\#[a-f0-9]{6}$"),
                r, g, b, rr, gg, bb, result;

            if (angular.isString(color) && color.match(ex)) {
                r = parseInt(color.substr(1, 2), 16);
                g = parseInt(color.substr(3, 2), 16);
                b = parseInt(color.substr(5, 2), 16);

                rr = Math.floor(r + b / 100 * rate);
                gg = Math.floor(g + b / 100 * rate);
                bb = Math.floor(b + b / 100 * rate);

                if (rr > 255) {
                    rr = 255
                }
                if (gg > 255) {
                    gg = 255
                }
                if (bb > 255) {
                    bb = 255
                }

                result = "#" +
                    ((rr < 16) ? "0" : "") + rr.toString(16) +
                    ((gg < 16) ? "0" : "") + gg.toString(16) +
                    ((bb < 16) ? "0" : "") + bb.toString(16);

            } else {

                result = null;

            }

            return result;
        }
    }]);

    site.directive("dBackgroundImage", [function () {
        return {
            scope: {
                dBackgroundImage: "@"
            },
            link: function ($scope, $element, $attrs) {
                $scope.$watch("dBackgroundImage", function (newUrl) {
                    $element.css({opacity: 0});
                    if (angular.isDefined(newUrl) && (!window.mobileCheck())) {
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
    }]);

    site.directive("morBackgroundColor", [function () {
        return {
            scope: {
                morBackgroundColor: "="
            },
            link: function ($scope, $element, $attributes) {
                $scope.$watch("morBackgroundColor", function (newColor) {
                    if (angular.isString(newColor)) {
                        $element.css("background-color", newColor + " !important");
                    } else {
                        $element.css("background-color", "");
                    }
                });
            }
        }
    }]);

    site.directive("morColor", [function () {
        return {
            scope: {
                morColor: "="
            },
            link: function ($scope, $element, $attributes) {
                $scope.$watch("morColor", function (newColor) {
                    if (angular.isDefined(newColor)) {
                        $element.css("color", newColor + " !important");
                    } else {
                        $element.css("color", "");
                    }
                });
            }
        }
    }]);

    site.directive("activeTab", ["$location", function ($location) {
        return {
            scope: {
                activeTab: "@"
            },
            link: function ($scope, $element, $attributes) {

                var CLASS = "active";

                $scope.$on("$routeChangeSuccess", function (event, currentRoute) {
                    $element.toggleClass(CLASS, $location.url().match($scope.activeTab) !== null);
                });

            }
        };
    }]);

    site.directive("multipleSelect", ["$parse", "$document", function ($parse, $document) {
        return {
            link: function ($scope, $element, $attr) {

                var CURRENT_CLASS = $attr["currentClass"] || "current";
                var SELECTED_CLASS = $attr["selectedClass"] || "selected";
                var DO_ON_TICK = $parse($attr["msTick"]);
                var SOURCE = $parse($attr["msSource"]);
                var DESTINATION = $parse($attr["msDestination"]);

                var selectNothing = function (event) {
                    if ($element.find(event.target).length == 0
                        && $(".select-persistent").find(event.target).length == 0
                        && $(".ngdialog").find(event.target).length == 0) {

                        $element.children().removeClass(SELECTED_CLASS).removeClass(CURRENT_CLASS);
                        updateSelection();
                    }
                };

                $scope.unSelect = function () {
                    $element.children().removeClass(SELECTED_CLASS).removeClass(CURRENT_CLASS);
                    updateSelection();
                };

                var updateSelection = function () {

                    var obj;

                    DESTINATION($scope).splice(0, DESTINATION($scope).length);

                    $element.children("." + SELECTED_CLASS).each(function () {
                        obj = SOURCE($scope)[$(this).index()];
                        DESTINATION($scope).push(obj);
                    });

                    $scope.$applyAsync(DO_ON_TICK);

                };

                $document.on("click", selectNothing);

                $scope.$on("$destroy", function () {
                    $document.unbind("click", selectNothing);
                });

                $element.live("mousedown", function (event) {

                    var ctrlPressed = event.metaKey || event.ctrlKey;
                    var shiftPressed = event.shiftKey;
                    var activeElement = $element.children().filter("." + CURRENT_CLASS);
                    var that = $(event.target).parents().filter($element.children());

                    var action = function () {

                        // Selection manipulation
                        if (ctrlPressed) {
                            $(this).toggleClass(SELECTED_CLASS);
                        } else if (shiftPressed) {
                            var fromIndex = activeElement.length ? activeElement.index() : 0;
                            var newIndex = $(this).index();
                            if (fromIndex < newIndex) {
                                $element.children().slice(fromIndex, newIndex).addClass(SELECTED_CLASS);
                            } else {
                                $element.children().slice(newIndex, fromIndex).addClass(SELECTED_CLASS);
                            }
                            $(this).addClass(SELECTED_CLASS);
                        } else {
                            $element.children().removeClass(SELECTED_CLASS);
                            $(this).addClass(SELECTED_CLASS);
                        }

                        // Change current
                        activeElement.removeClass(CURRENT_CLASS);
                        $(this).addClass(CURRENT_CLASS);

                        // Do on Tick
                        updateSelection();

                    };

                    var right = function () {
                        if (activeElement.length == 0) {
                            $(this).addClass(CURRENT_CLASS).addClass(SELECTED_CLASS);
                        }
                        updateSelection();
                    };


                    if (event.button == 0) {
                        action.call(that);
                    } else if (event.button == 2) {
                        right.call(that);
                    }

                    event.stopPropagation();
                    event.preventDefault();

                    return false;

                });

            }
        };
    }]);

    site.directive("ensureUnique", ["$http", function ($http) {
        var timer = false;
        return {
            require: 'ngModel',
            link: function (scope, elem, attrs, c) {
                scope.$watch(attrs.ngModel, function (n) {

                    if (typeof timer == "number") {
                        clearTimeout(timer);
                        timer = false;
                    }

                    if (!n) {
                        return false;
                    }

                    timer = setTimeout(function () {
                        $http({
                            method: "POST",
                            url: "/api/check/" + attrs.ensureUnique,
                            data: {field: elem.val()}
                        }).success(function (res) {
                            c.$setValidity('unique', res.data.available);
                        }).error(function () {
                            c.$setValidity('unique', false);
                        });
                    }, 250);
                })
            }
        }
    }]);


    site.directive("mustExist", ["$http", function ($http) {
        var timer = false;
        return {
            require: 'ngModel',
            link: function (scope, elem, attrs, c) {
                scope.$watch(attrs.ngModel, function (n) {

                    if (typeof timer == "number") {
                        clearTimeout(timer);
                        timer = false;
                    }

                    if (!n) {
                        return false;
                    }

                    timer = setTimeout(function () {
                        $http({
                            method: "POST",
                            url: "/api/exists/" + attrs.mustExist,
                            data: {field: elem.val()}
                        }).success(function (res) {
                            c.$setValidity('exists', res.data.exists);
                        }).error(function () {
                            c.$setValidity('exists', true);
                        });
                    }, 250);
                })
            }
        }
    }]);

    site.directive("isAvailable", ["$http", function ($http) {
        var timer = false;
        return {
            require: 'ngModel',
            link: function (scope, elem, attrs, c) {
                scope.$watch(attrs.ngModel, function (n) {

                    if (timer !== false) {
                        clearTimeout(timer);
                        timer = false;
                    }

                    if (!n) {
                        return false;
                    }

                    timer = setTimeout(function () {
                        $http({
                            method: "POST",
                            url: "/api/check/" + attrs.isAvailable,
                            data: {field: elem.val(), context: attrs.morContext}
                        }).success(function (res) {
                            c.$setValidity('available', res.data.available);
                        }).error(function () {
                            c.$setValidity('available', false);
                        });
                    }, 250);
                })
            }
        }
    }]);

    site.directive("ngFocus", [function () {
        var FOCUS_CLASS = "ng-focused";
        return {
            restrict: 'A',
            require: 'ngModel',
            link: function (scope, elem, attrs, c) {
                c.$focused = false;
                elem.bind("focus", function () {
                    elem.addClass(FOCUS_CLASS);
                    scope.$apply(function () {
                        c.$focused = true;
                    });
                }).bind("blur", function () {
                    elem.removeClass(FOCUS_CLASS);
                    scope.$apply(function () {
                        c.$focused = false;
                    })
                })
            }
        }
    }]);

    site.directive('ngEnter', [function () {
        return function (scope, element, attrs) {
            element.bind("keydown keypress", function (event) {
                if (event.which === 13) {
                    scope.$apply(function () {
                        scope.$eval(attrs.ngEnter);
                    });

                    event.preventDefault();
                }
            });
        };
    }]);

    site.filter("repeat", [function () {
        return function (input) {

        };
    }]);

    site.filter('bytes', ["$rootScope", function ($rootScope) {
        return function (bytes, precision) {
            if (isNaN(parseFloat(bytes)) || !isFinite(bytes)) return '-';
            if (typeof precision === 'undefined') precision = 1;
            var units = $rootScope.tr("TR_BYTES_METRICS"),
                number = Math.floor(Math.log(bytes) / Math.log(1024));
            return (bytes / Math.pow(1024, Math.floor(number))).toFixed(precision) + ' ' + units[number];
        }
    }]);

    site.filter('userDisplayName', [function () {
        return function (user) {
            return (typeof user != "undefined") ? (user.name ? user.name : user.login) : undefined;
        }
    }]);


    site.filter('regExpEscape', [function () {
        return function (str) {
            return str.toString().replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, "\\$&");
        }
    }]);


    site.directive("compareTo", [function () {
        return {
            require: "ngModel",
            scope: {
                otherModelValue: "=compareTo"
            },
            link: function (scope, element, attributes, ngModel) {

                ngModel.$validators.compareTo = function (modelValue) {
                    return modelValue == scope.otherModelValue;
                };

                scope.$watch("otherModelValue", function () {
                    ngModel.$validate();
                });
            }
        };
    }]);

    site.directive("popup", [function () {
        return {
            link: function (scope, element, attrs) {
                var link = attrs.href;
                var size = attrs.popup.split("x");

                element.on("click", function (event) {
                    window.open(link, "", "width=".concat(size[0]).concat(", ").concat("height=").concat(size[1]));
                    event.stopPropagation();
                    event.preventDefault();
                    return false;
                });
            }
        };
    }]);

    site.directive("morSuggest", ["$rootScope", function ($rootScope) {
        return {
            scope: {
                morSuggest: "=",
                ngModel: "="
            },
            require: "ngModel",
            link: function ($scope, $element, $attributes) {
                var old;
                $element.on("keyup", function (event) {
                    if (event.which < 32 || event.which == 46) return;
                    if (old != event.target.value) {
                        var position = event.target.selectionStart;
                        var temp = $rootScope.lib.genres;
                        for (var i = 0, length = temp.length; i < length; i += 1) {
                            if (temp[i].genre_name.toLowerCase().indexOf(event.target.value.toLowerCase()) == 0) {
                                $scope.ngModel = temp[i].genre_name;
                                $scope.$apply();
                                event.target.selectionStart = position;
                                break;
                            }
                        }
                    }
                    old = event.target.value;
                })
            }
        }
    }]);

    site.directive("sync", [function () {
        return {
            scope: {
                source: "=",
                destination: "="
            },
            link: function ($scope) {
                $scope.$watch("source", function (value) {
                    if (angular.isArray(value) && angular.isArray($scope.destination)) {
                        copyArrayValues(value, $scope.destination);
                    } else if (angular.isObject(value) && angular.isObject($scope.destination)) {
                        copyObjectValues(value, $scope.destination);
                    } else {
                        $scope.destination = value;
                    }
                });
            }
        }
    }]);

    site.directive("followVertical", ["$document", function ($document) {
        return {
            link: function ($scope, $element, $attributes) {
                var scrollFollow = function (event) {
                    $element.css("padding-top", $document.scrollTop());
                };
                $document.on("scroll", scrollFollow);
                $scope.$on("$destroy", function () {
                    $document.unbind("scroll", scrollFollow)
                });

            }
        }
    }]);

    site.directive("morEdit", ["$timeout", "$document", function ($timeout, $document) {
        return {
            scope: {
                morEdit: "=",
                morEditSubmit: "&"
            }
        }
    }]);

    site.factory("Response", [function () {
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
    }]);

    site.factory("$body", [function () {
        return angular.element("body");
    }]);

    site.factory("$dialog", ["ngDialog", function (ngDialog) {
        return {
            question: function (question, callback) {
                if (typeof question != "string")
                    throw new Error("Question must be a STRING");

                if (typeof callback != "function")
                    throw new Error("Callback must be a FUNCTION");

                ngDialog.openConfirm({
                    template: '\
                    <div class="dialog-wrap">\
                        <i class="big-icon icon-question"></i>\
                        <div class="dialog-body">' + question + '</div>\
                        <div class="buttons">\
                            <span class="button" ng-click="confirm(1)"><translate>FR_YES</translate></span>\
                            <span class="button" ng-click="closeThisDialog()"><translate>FR_NO</translate></span>\
                        </div>\
                    </div>',
                    plain: true,
                    showClose: false
                }).then(function () {
                    callback.call();
                });

            },
            info: function (info) {
                if (typeof info != "string")
                    throw new Error("Info must be a STRING");

                ngDialog.openConfirm({
                    template: '\
                    <div class="dialog-wrap">\
                        <div class="dialog-body">' + info + '</div>\
                        <div class="buttons">\
                            <span class="button" ng-click="closeThisDialog()"><translate>FR_OK</translate></span>\
                        </div>\
                    </div>',
                    plain: true,
                    showClose: false
                });
            }

        };
    }]);

    site.directive("ngChangeAction", ["$timeout", function ($timeout) {
        var timer;
        return {
            require: 'ngModel',
            link: function (scope, element, attr, ctrl) {
                ctrl.$viewChangeListeners.push(function () {
                    if (timer) $timeout.cancel(timer);
                    timer = $timeout(function () {
                        scope.$eval(attr.ngChangeAction);
                    }, attr.ngChangeDelay || 0);
                });
            }
        }
    }])

})();