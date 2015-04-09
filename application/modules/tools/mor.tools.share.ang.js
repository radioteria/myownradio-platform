(function () {
    var tools = angular.module("mor.tools");

    tools.controller("StreamShareController", ["$scope", "$timeout", function ($scope, $timeout) {

        $scope.$watch("maxSize", function (width) {
            $scope.code = '<iframe ng-src="https://myownradio.biz/widget/?stream_id=' + $scope.streamObject.sid +
                '" width="' + $scope.embed.maxSize + '" height="' + $scope.embed.maxSize + '"></iframe>';
        });

        $scope.embed = {
            url: "/widget/?stream_id=" + $scope.streamObject.sid,
            maxSize: 400
        };

        $timeout(function () {
            stButtons.locateElements();
        }, 100);

    }]);

    tools.directive("shareChannel", [function () {
        return {
            scope: {
                shareObject: "=shareChannel"
            },
            restrict: "A",
            replace: true,
            template: "<i class=\"icon-share-alt\" mor-tooltip=\"{{ tr('FR_SHARE_THIS') }}\" ng-click=\"share()\"></i>",
            controller: ["$scope", "ngDialog", "$rootScope", function ($scope, ngDialog, $rootScope) {
                $scope.tr = $rootScope.tr;
                $scope.share = function () {
                    if (angular.isDefined($scope.shareObject)) {
                        var scope = $scope.$new();
                        scope.streamObject = $scope.shareObject;
                        scope.streamObject.url = "https://myownradio.biz/streams/" + scope.streamObject.key;
                        scope.tr = $rootScope.tr;
                        ngDialog.open({
                            templateUrl: "/views/blocks/share.html",
                            controller: "StreamShareController",
                            scope: scope
                        });
                    }
                }
            }]
        }
    }]);

})();