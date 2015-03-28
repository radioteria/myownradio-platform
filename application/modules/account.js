/**
 * Module Account
 */
(function () {

    var account = angular.module("Account", ["Site"]);

    account.run(["$rootScope", "User", "$location", "$route", "$cacheFactory", "$http",
        function ($rootScope, User, $location, $route, $cacheFactory, $http) {

            // Initial account state
            $rootScope.account = {authorized: false, user: null, pending: true, streams: null, client_id: null};

            $rootScope.$watch("account.pending", function (value) {

                if (typeof $route.current == "undefined") return;
                if (typeof $route.current.needsAuth == "undefined") return;
                if (value == true) return;

                if ($rootScope.account.authorized == false && $route.current.needsAuth === true) {
                    $location.url("/login");
                }

            });

            $rootScope.$on("$routeChangeSuccess", function (event, currentRoute) {

                $rootScope.account.init();

            });

            // Function handlers
            $rootScope.account.init = function (go) {

                $rootScope.account.pending = true;

                $cacheFactory.get('$http').removeAll();

                var cookie = $.cookie();

                User.whoAmI().onSuccess(function (data) {
                    $rootScope.account.authorized = true;
                    $rootScope.account.user = data.user;
                    $rootScope.account.streams = data.streams;
                    $rootScope.account.pending = false;
                    $rootScope.account.client_id = data.client_id;

                    if (typeof go == "string") {
                        $location.url(go);
                    }
                }, function () {
                    $rootScope.account.authorized = false;
                    $rootScope.account.user = null;
                    $rootScope.account.streams = null;
                    $rootScope.account.pending = false;
                    $rootScope.account.client_id = null;
                });
            };

            $rootScope.account.logout = function () {
                $cacheFactory.get('$http').removeAll();
                User.logout().onSuccess(function () {
                    //$rootScope.account.init();
                    $route.reload();
                });
            };

        }]);

    /*
     Login Controller
     */
    account.controller("LoginForm", ["$scope", "$rootScope", "User", "$location", "Popup", "Account",

        function ($scope, $rootScope, User, $location, Popup, Account) {

            // Init variables
            $scope.credentials = {login: "", password: "", save: false};
            $scope.status = "";
            $scope.submit = function () {
                User.login($scope.credentials)
                    .onSuccess(function (data) {
                        Popup.message("Welcome, " + data.name + "!<br>You're successfully logged in!");
                        $scope.status = "";
                        $rootScope.account.init("/profile/");
                    }, function (err) {
                        $scope.status = err;
                    }
                )
            };
            $scope.signUpFacebook = function () {
                FB.login(function (response) {
                    if (response.status === "connected") {
                        Account.loginByFacebook(response.authResponse).onSuccess(function(data) {
                            Popup.message("Welcome, " + data.name + "!<br>You're successfully logged in!");
                            $scope.status = "";
                            $scope.account.init("/profile/");
                        }, function (error) {
                            $scope.status = error;
                        });
                    }
                }, {
                    scope: "email"
                });
            };
        }

    ]);

    account.controller("ChangePlanController", ["$scope", "User", "$location", "Popup",

        function ($scope, User, $location, Popup) {
            $scope.data = {
                code: "",
                error: ""
            };
            $scope.submit = function () {
                User.enterPromoCode($scope.data.code).onSuccess(function (data) {
                    Popup.message("You have successfully changed your current account plan to" + htmlEscape(data));
                    $location.url("/profile/");
                }, function (message) {
                    $scope.data.error = message;
                })
            }
        }

    ]);

    /*
     Sign Up Controllers
     */
    account.controller("SignUpBeginForm", ["$scope", "$location", "Account", "Popup",

        function ($scope, $location, Account, Popup) {

            // Init variables
            $scope.signup = {email: "", code: ""};
            $scope.status = "";
            $scope.submit = function () {
                Account.signUpRequest($scope.signup).onSuccess(function () {
                    $scope.status = "";
                    $location.url("/static/registrationLetterSent");
                }, function (err) {
                    $scope.status = err;
                });
            };

            $scope.signUpFacebook = function () {
                FB.login(function (response) {
                    if (response.status === "connected") {
                        Account.loginByFacebook(response.authResponse).onSuccess(function (data) {
                            Popup.message("Welcome, " + data.name + "!<br>You're successfully logged in!");
                            $scope.account.init("/profile/");
                        }, function (error) {
                            console.log(error);
                        });
                    }
                }, {
                    scope: "email"
                });
            };

        }

    ]);

    account.controller("SignUpCompleteForm", ["$scope", "$location", "Account", "$routeParams",

        function ($scope, $location, Account, $routeParams) {
            // Init variables
            $scope.signup = {code: $routeParams.code, login: "", password: "", name: "", info: "", permalink: "", country_id: null};
            $scope.status = "";
            $scope.submit = function () {
                Account.signUp($scope.signup).onSuccess(function () {
                    $scope.status = "";
                    $location.url("/static/registrationCompleted");
                }, function (err) {
                    $scope.status = err;
                });
            }
        }

    ]);

    /*
     Password Recovery Controllers
     */
    account.controller("PasswordResetBeginForm", ["$scope", "$location", "Account",

        function ($scope, $location, Account) {
            // Init variables
            $scope.reset = {login: ""};
            $scope.status = "";
            $scope.submit = function () {
                Account.resetRequest($scope.reset).onSuccess(function () {
                    $scope.status = "";
                    $location.url("/static/resetLetterSent"); // todo: add email hint
                }, function (err) {
                    $scope.status = err;
                });
            }
        }

    ]);

    account.controller("PasswordResetCompleteForm", ["$scope", "$location", "Account", "$routeParams",

        function ($scope, $location, Account, $routeParams) {
            // Init variables
            $scope.reset = {code: $routeParams.code, password: ""};
            $scope.check = "";
            $scope.status = "";
            $scope.submit = function () {
                Account.resetPassword($scope.reset).onSuccess(function () {
                    $scope.status = "";
                    $location.url("/static/resetPasswordCompleted");
                }, function (err) {
                    $scope.status = err;
                });
            }
        }

    ]);

    account.factory("Account", ["$http", "Response", function ($http, Response) {
        return {
            loginByFacebook: function (authResponse) {
                var action = $http({
                    method: 'POST',
                    url: "/api/v2/user/fbLogin",
                    data: {
                        token: authResponse.accessToken
                    }
                });
                return Response(action);
            },
            signUpRequest: function (params) {
                var action = $http({
                    method: 'POST',
                    url: "/api/v2/user/signUpBegin",
                    data: params
                });
                return Response(action);
            },
            signUp: function (params) {
                var action = $http({
                    method: "POST",
                    url: "/api/v2/user/signUpComplete",
                    data: {
                        code: params.code,
                        login: params.login,
                        password: params.password,
                        name: params.name,
                        info: params.info,
                        permalink: params.permalink,
                        country_id: params.country_id
                    }
                });
                return Response(action);
            },
            resetRequest: function (params) {
                var action = $http({
                    method: "POST",
                    url: "/api/v2/user/passwordResetBegin",
                    data: {
                        login: params.login
                    }
                });
                return Response(action);
            },
            resetPassword: function (params) {
                var action = $http({
                    method: "POST",
                    url: "/api/v2/user/passwordResetComplete",
                    data: {
                        code: params.code,
                        password: params.password
                    }
                });
                return Response(action);
            }
        };
    }]);

    /**
     * @class User
     * @constructor
     */
    account.factory("User", ["$http", "Response", function ($http, Response) {
        return {
            /**
             * @method whoAmI
             * @returns {*}
             */
            whoAmI: function () {
                var action = $http({
                    method: "GET",
                    url: "/api/v2/self"
                });
                return Response(action);
            },
            /**
             * @method changeInfo
             * @param name
             * @param info
             * @param permalink
             * @param country
             * @returns {*}
             */
            changeInfo: function (name, info, permalink, country) {
                var action = $http({
                    method: "POST",
                    url: "/api/v2/self",
                    data: {
                        name: name,
                        info: info,
                        permalink: permalink,
                        country_id: country
                    }
                });
                return Response(action);
            },
            /**
             * @method changePassword
             * @param new_password
             * @param old_password
             * @returns {*}
             */
            changePassword: function (new_password, old_password) {
                var action = $http({
                    method: "POST",
                    url: "/api/v2/self/changePassword",
                    data: {
                        new_password: new_password,
                        old_password: old_password
                    }
                });
                return Response(action);
            },
            /**
             * @method deleteAccount
             * @param password
             * @returns {*}
             */
            deleteAccount: function (password) {
                var action = $http({
                    method: "POST",
                    url: "/api/v2/self/delete",
                    data: {
                        password: password
                    }
                });
                return Response(action);
            },
            /**
             * @method login
             * @param data
             * @returns {*}
             */
            login: function (data) {
                var action = $http({
                    method: "POST",
                    url: "/api/v2/user/login",
                    data: data
                });
                return Response(action);
            },
            /**
             * @method logout
             * @returns {*}
             */
            logout: function () {
                var action = $http({
                    method: "DELETE",
                    url: "/api/v2/self"
                });
                return Response(action);
            },
            /**
             * @method enterPromoCode
             * @param code
             * @returns {*}
             */
            enterPromoCode: function (code) {
                return Response($http({
                    method: "POST",
                    url: "/api/v2/self/promoCode",
                    data: {
                        code: code
                    }
                }))
            }
        }
    }]);

    account.factory("Bookmarks", ["$http", "Response", function ($http, Response) {
        return {
            add: function (streamObject) {
                var action = $http({
                    method: "PUT",
                    url: "/api/v2/bookmark",
                    params: {
                        stream_id: streamObject.sid
                    }
                });
                return Response(action);
            },
            remove: function (streamObject) {
                var action = $http({
                    method: "DELETE",
                    url: "/api/v2/bookmark",
                    params: {
                        stream_id: streamObject.sid
                    }
                });
                return Response(action);
            }
        }
    }]);

    account.directive("bookmark", ["Bookmarks", "Popup", "$rootScope",

        function (Bookmarks, Popup, $rootScope) {

            var buttonTemplate = '<i ng-if="!stream.bookmarked" class="icon-heart-o" mor-tooltip="Add radio station to bookmarks"></i>\
                                  <i ng-if=" stream.bookmarked" class="icon-heart" mor-tooltip="Remove radio station from bookmarks"></i>';

            return {
                restrict: "E",
                scope: {
                    stream: "=ngModel"
                },
                require: "ngModel",
                template: buttonTemplate,
                link: function ($scope, $element, $attrs) {
                    $element.on("click", function (event) {
                        if ($scope.stream.bookmarked) {
                            Bookmarks.remove($scope.stream).onSuccess(function () {
                                $rootScope.$broadcast("BOOKMARK", {id: $scope.stream.sid, bookmarked: false});
                                Popup.message("<b>" + htmlEscape($scope.stream.name) + "</b> successfully removed from your bookmarks");
                            }, function (message) {
                                Popup.message(message, "Error");
                            });
                        } else {
                            Bookmarks.add($scope.stream).onSuccess(function () {
                                $rootScope.$broadcast("BOOKMARK", {id: $scope.stream.sid, bookmarked: true});
                                Popup.message("<b>" + htmlEscape($scope.stream.name) + "</b> successfully added to your bookmarks");
                            }, function () {
                                Popup.message("To use this feature please login");
                            });
                        }
                    });
                },
                controller: ["$scope", function ($scope) {
                        $scope.$on("BOOKMARK", function (broadcast, data) {
                            if ($scope.stream.sid == data.id) {
                                $scope.stream.bookmarked = data.bookmarked;
                            }
                        })
                }]
            }
        }

    ]);

})();

