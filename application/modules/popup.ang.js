(function () {

    var POPUP_TIMEOUT_DEFAULT = 5000;

    angular.module("mor-popup", [])

        .directive("morPopupFrame", ["$timeout", function ($timeout) {

            var timerHandle;

            return {
                restrict: 'E',
                templateUrl: "/views/blocks/popup.html",
                replace: true,
                scope: {
                    morPopupText: "@",
                    hideAfter: "="
                },
                link: function (scope, element) {
                    element.css('left', '400px').animate({left: '0'}, 200);

                    if (scope.hideAfter) {
                        timerHandle = $timeout(function () {
                            element.animate({left: '400px'}, 400, function () {
                                $(this).remove();
                            });
                        }, scope.hideAfter)
                    }
                    // Listen for scope destroy event
                    scope.$on("$destroy", function () {
                        $timeout.cancel(timerHandle);
                    });

                }
            }

        }])

        .directive("bindHtml", [function () {
            return {
                restrict: "A",
                scope: {
                    bindHtml: "="
                },
                link: function ($scope, $element, $attrs) {
                    $scope.$watch("bindHtml", function (newValue) {
                        $element.html(newValue);
                    })
                }
            }
        }])

        .factory("Popup", ["$body", "$compile", "$rootScope", function ($body, $compile, $rootScope) {

            var popupBackgroundElement = angular.element('<div class="popup-background"></div>').prependTo($body);

            return {
                message: function (message, timeout) {
                    var elem = angular.element("<mor-popup-frame>");
                    elem.attr("mor-popup-text", message);
                    if (timeout) {
                        elem.attr("hide-after", timeout);
                    } else {
                        elem.attr("hide-after", POPUP_TIMEOUT_DEFAULT);
                    }
                    elem.on("click", function () {
                        elem.remove();
                    });
                    $compile(elem)($rootScope);
                    elem.appendTo(popupBackgroundElement);
                }
            }
        }])

})();