/**
 * Created by Roman on 27.03.15.
 */
(function () {
    var module = angular.module("mor.tools");

    module.controller("ChangeImageController", ["$scope", function ($scope) {

    }]);

    module.factory("ImageChanger", ["$rootScope", "ngDialog", function ($rootScope, ngDialog) {
        return function (src, callback) {
            var $scope = $rootScope.$new();
            $scope.data = {
                url: src || null,
                callback: callback || null
            };
            ngDialog({
                template: "/views/blocks/change-image.html",
                controller: "ChangeImageController",
                scope: $scope
            });
        }
    }]);

})();