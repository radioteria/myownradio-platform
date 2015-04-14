/**
 * Created by roman on 14.04.15.
 */

(function () {
    var module = angular.module("application");
    module.directive("hashtags", [function () {
        return {
            scope: {
                ngModel: "="
            },
            restrict: "E",
            template: '<span class="tags"><span class="tag" ng-repeat="tag in tags" ng-bind="tag" ng-click="tags.splice($index, 1)"></span></span><span class="current-tag" contenteditable="true"></span>',
            require: "?ngModel",
            link: function (scope, element, attrs) {
                var update = function () {
                        scope.ngModel = scope.tags.join(",");
                    },
                    push = function (that) {
                        if (that.innerHTML.length) {
                            if (that.innerHTML.substr(0, 1) == "#") {
                                scope.tags.push(that.innerHTML.substr(1));
                            } else {
                                scope.tags.push(that.innerHTML);
                            }
                            update();
                            that.innerHTML = "";
                        }
                    };
                scope.tags = [];
                scope.focused = false;
                scope.placeholder = attrs.placeholder;
                element.find(".current-tag")
                    .bind("keydown", function (event) {
                        var that = this;
                        switch (event.which) {
                            case 8: // del key
                                if (that.innerHTML.length == 0) {
                                    scope.$apply(function () {
                                        scope.tags.splice(-1, 1);
                                        update();
                                    });
                                    return false;
                                }
                                break;
                            case 13: // enter key
                            case 188: // comma key
                                scope.$apply(function () {
                                    push(that);
                                });
                                return false;
                        }
                        return true;
                    })
                    .bind("blur", function (event) {
                        var that = this;
                        scope.$apply(function () {
                            scope.focused = false;
                            push(that);
                        });
                    })
                    .bind("focus", function (event) {
                        scope.$apply(function () {
                            scope.focused = true;
                        });
                    });
                scope.$watch("ngModel", function (value) {
                    if (angular.isString(value) && value.length) {
                        scope.tags = value.split(",").map(function (untrimmed) {
                            return untrimmed.trim()
                        });
                    } else {
                        scope.tags = [];
                    }
                });
            }
        }
    }])
})();