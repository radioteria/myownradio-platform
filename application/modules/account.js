/**
 * Module Account
 */
(function () {

    var account = angular.module("Account", ["Site"]);

    account.run(["$rootScope", "User", "$location", "$route", "$cacheFactory", "$http", "$mixpanel",
        function ($rootScope, User, $location, $route, $cacheFactory, $http, $mixpanel) {

            // Initial account state
            $rootScope.account = {authorized: false, user: null, pending: true, streams: null, client_id: null};

            $rootScope.$watch("account.pending", function (value) {

                if (typeof $route.current == "undefined") return;
                if (typeof $route.current.needsAuth == "undefined") return;
                if (value == true) return;

                if ($rootScope.account.authorized == false && $route.current.needsAuth === true) {
                    $mixpanel.track("Redirecting to login page");
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

                    $mixpanel.identify(data.user.id);
                    $mixpanel.people.set({
                        '$email': data.user.email,
                        '$first_name': data.user.name
                    });

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
                    $mixpanel.track("Logout");
                    $route.reload();
                });
            };

        }]);

    /*
     Login Controller
     */
    account.controller("LoginForm", ["$scope", "$rootScope", "User", "$location", "Popup", "Account", "$analytics", "$mixpanel",

        function ($scope, $rootScope, User, $location, Popup, Account, $analytics, $mixpanel) {

            // Init variables
            $scope.credentials = {login: "", password: "", save: false};
            $scope.status = "";
            $scope.submit = function () {
                User.login($scope.credentials)
                    .onSuccess(function (data) {
                        $analytics.eventTrack('Login', { category: 'Actions' });
                        Popup.message($scope.tr("FR_LOGIN_MESSAGE", data));
                        $mixpanel.track("User login");
                        $scope.status = "";
                        $rootScope.account.init("/profile/");
                        if (typeof $location.search().go != "undefined") {
                            $rootScope.account.init($location.search().go);
                        } else {
                            $rootScope.account.init("/profile/");
                        }
                    }, function (err) {
                        console.log("error");
                        $scope.status = err;
                    }
                )
            };
            $scope.signUpFacebook = function () {
                FB.login(function (response) {
                    if (response.status === "connected") {
                        Account.loginByFacebook(response.authResponse).onSuccess(function(data) {
                            $mixpanel.track("User login using Facebook");
                            Popup.message($scope.tr("FR_LOGIN_MESSAGE", data));
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
                    Popup.message($scope.tr("FR_PLAN_CHANGED_MESSAGE", [data]));
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
    account.controller("SignUpBeginForm", ["$scope", "$location", "Account", "Popup", "$mixpanel",

        function ($scope, $location, Account, Popup, $mixpanel) {
            // Init variables
            $scope.signup = {email: "", code: ""};
            $scope.status = "";
            $scope.submit = function () {
                $mixpanel.track("Submitting sign up form");
                Account.signUpRequest($scope.signup).onSuccess(function () {
                    $scope.status = "";
                    $location.url("/static/registrationLetterSent");
                }, function (err) {
                    $scope.status = err;
                });
            };

            $scope.signUpFacebook = function () {
                $mixpanel.track('Signing up using facebook');
                FB.login(function (response) {
                    if (response.status === "connected") {
                        Account.loginByFacebook(response.authResponse).onSuccess(function (data) {
                            $mixpanel.track("User registration using Facebook");
                            Popup.message($scope.tr("FR_LOGIN_MESSAGE", data));
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

    account.controller("SignUpCompleteForm", ["$scope", "$location", "Account", "$routeParams", "$mixpanel",

        function ($scope, $location, Account, $routeParams, $mixpanel) {
            $mixpanel.track('Viewing sign up completion page');
            // Init variables
            $scope.signup = {code: $routeParams.code, login: "", password: "", name: "", info: "", permalink: "", country_id: null};
            $scope.status = "";
            $scope.submit = function () {
                $mixpanel.track('Completing signing up');
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
    account.controller("PasswordResetBeginForm", ["$scope", "$location", "Account", "$mixpanel",

        function ($scope, $location, Account, $mixpanel) {
            // Init variables
            $scope.reset = {login: ""};
            $scope.status = "";
            $scope.submit = function () {
                $mixpanel.track("Sends password reset request");

                Account.resetRequest($scope.reset).onSuccess(function () {
                    $scope.status = "";
                    $location.url("/static/resetLetterSent"); // todo: add email hint
                }, function (err) {
                    $scope.status = err;
                });
            }
        }

    ]);

    account.controller("PasswordResetCompleteForm", ["$scope", "$location", "Account", "$routeParams", "$mixpanel",

        function ($scope, $location, Account, $routeParams, $mixpanel) {
            // Init variables
            $scope.reset = {code: $routeParams.code, password: ""};
            $scope.check = "";
            $scope.status = "";
            $scope.submit = function () {
                $mixpanel.track('Completing password reset');
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

            var buttonTemplate = '<i ng-if="!stream.bookmarked" class="icon-heart-o" mor-tooltip="{{ $root.tr(\'FR_BOOKMARK_ADD_TIP\') }}"></i>\
                                  <i ng-if=" stream.bookmarked" class="icon-heart" mor-tooltip="{{ $root.tr(\'FR_BOOKMARK_REMOVE_TIP\') }}"></i>';

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
                                Popup.message($rootScope.tr("FR_BOOKMARK_REMOVE_SUCCESS", [ $scope.stream.name ]));
                            }, function (message) {
                                Popup.message(message);
                            });
                        } else {
                            Bookmarks.add($scope.stream).onSuccess(function () {
                                $rootScope.$broadcast("BOOKMARK", {id: $scope.stream.sid, bookmarked: true});
                                Popup.message($rootScope.tr("FR_BOOKMARK_ADD_SUCCESS", [ $scope.stream.name ]));
                            }, function (message) {
                                Popup.message(message);
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

