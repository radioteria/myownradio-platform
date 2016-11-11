(function () {
    var tools = angular.module("mor.tools", []);

    tools.directive("ngClickOutside", ["$document", "$parse", function ($document, $parse) {
            return {
                restrict: 'A',
                multiElement: true,
                link: function (scope, element, attrs) {

                    var action = function (event) {
                        if (element.find(event.target).length == 0) {
                            scope.$apply(function () {
                                var action = $parse(attrs.ngClickOutside);
                                action(scope);
                            });
                        }
                    };

                    $document.on("click", action);

                    scope.$on("$destroy", function () {
                        $document.unbind("click", action);
                    });

                }
            };
        }
    ]);


    tools.directive("ngVisible", [function () {
        return {
            restrict: "A",
            scope: {
                ngVisible: "="
            },
            link: function (scope, element, attrs) {
                scope.$watch("ngVisible", function (value) {
                    element.css("visibility", value ? "visible" : "hidden");
                });
            }
        }
    }]);

    tools.factory("ResponseData", [function () {
        return function (res) {
            var response = res.data;
            return {
                onSuccess: function (onSuccess, onError) {
                    onSuccess = onSuccess || function () {
                    };
                    onError = onError || function () {
                    };
                    if (response.code == 1) {
                        onSuccess(response.data, response.message);
                    } else {
                        onError(response.message);
                    }
                }
            }
        }
    }]);


})();