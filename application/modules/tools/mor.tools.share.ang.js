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

    tools.directive("share", [function () {
        return {
            scope: {
                ngModel: "="
            },
            restrict: "E",
            required: "ngModel",
            template: "<i class='icon-share-alt' mor-tooltip='Share this radio channel' ng-click='share()'></i>",
            controller: ["$scope", "ngDialog", function ($scope, ngDialog) {
                $scope.share = function () {
                    if (angular.isDefined($scope.ngModel)) {
                        var scope = $scope.$new();
                        scope.streamObject = $scope.ngModel;
                        scope.streamObject.url = "https://myownradio.biz/streams/" + scope.streamObject.key;
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