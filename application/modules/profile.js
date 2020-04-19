(function () {

    var profile = angular.module("Profile", ["Site"]);

    profile.controller("ProfileController", ["$rootScope", "$scope", "User",

        function ($rootScope, $scope, User) {

            $scope.status = "";
            $scope.error = "";

            var watcher = $rootScope.$watch("account.user", function (value) {
                $scope.details = value;
            });

            $scope.submit = function () {
                $scope.status = "";
                $scope.error = "";
                User.changeInfo($scope.details.name, $scope.details.info, $scope.details.permalink, $scope.details.country_id)
                    .onSuccess(function () {
                        $scope.status = $scope.tr("FR_PROFILE_UPDATED");
                    }, function (err) {
                        $scope.error = err;
                    });
            };

            $scope.$on("$destroy", function () {
                watcher();
            });

        }

    ]);

    profile.controller("ChangePasswordController", ["$scope", "User", function ($scope, User) {

        $scope.status = "";
        $scope.error = "";

        $scope.passwords = {
            current: "",
            password1: ""
        };
        $scope.submit = function () {
            $scope.status = "";
            $scope.error = "";
            User.changePassword($scope.passwords.password1, $scope.passwords.current)
                .onSuccess(function () {
                    $scope.status = $scope.tr("FR_PASSWORD_CHANGED");;
                }, function (err) {
                    $scope.error = err;
                });
        };
    }]);

    profile.controller("UserAvatarController", ["$rootScope", "$scope", "$http", "Response",

        function ($rootScope, $scope, $http, Response) {

            $scope.avatarUrl = null;

            var watcher = $rootScope.$watch("account.user.avatar_url", function (url) {
                $scope.avatarUrl = url;
            });

            $scope.$on("$destroy", function () {
                watcher();
            });

            $scope.upload = function () {
                var file = $("<input>");
                file.attr("type", "file");
                file.attr("accept", "image/jpeg,image/png")
                file.on("change", function (event) {
                    if (this.files.length == 0) return;

                    var fd = new FormData();
                    fd.append('file', this.files[0]);

                    var uploader = Response($http({
                        method: "POST",
                        url: "/api/v2/avatar",
                        data: fd,
                        transformRequest: angular.identity,
                        headers: {'Content-Type': undefined}
                    }));

                    uploader.onSuccess(function (url) {
                        $scope.avatarUrl = url;
                        $rootScope.account.init();
                    });

                });
                file.click();
            };

            $scope.remove = function () {

                var uploader = Response($http({
                    method: "DELETE",
                    url: "/api/v2/avatar"
                }));

                uploader.onSuccess(function () {
                    $scope.avatarUrl = null;
                    $rootScope.account.init();
                });

            };
        }

    ]);

    profile.controller("NavigationController", ["$scope", function ($scope) {

    }]);

    profile.directive("mainNavigation", [function () {
        return {
            templateUrl: "/views/blocks/nav.html",
            controller: "NavigationController"
        }
    }]);

})();