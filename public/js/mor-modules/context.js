(function () {
    var module = angular.module("mor-context-menu", []);
    module.directive("contextMenu", [
        function () {
            return {
                restrict: "A",
                scope: {
                    callback: "&contextMenu",
                    disabled: "&contextMenuDisabled"
                },
                link: function ($scope, $element, $attrs) {
                    $element.bind('contextmenu', function(event) {
                        if (!$scope.disabled()) {
                            event.preventDefault();
                            event.stopPropagation();
                            $scope.$apply(function() {
                                $scope.callback({ $event: event, $element: $element });
                            });
                        }
                    });
                }
            }
        }
    ]);
})();