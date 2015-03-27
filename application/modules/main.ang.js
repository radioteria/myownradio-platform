/**
 * Created by Roman on 08.12.2014.
 */

var SITE_TITLE =  "MyOwnRadio - Your own web radio station";

(function () {

    var md = angular.module("application", [

        "ngRoute", "ngAnimate", "ngDialog", "ngTouch",
        "angular-loading-bar", 'angulartics', 'angulartics.google.analytics',
        "httpPostFix", "infinite-scroll", "ng-context-menu", "ui.sortable", 'seo', "mor-popup",

        "Account", "Site", "Catalog", "RadioPlayer", "Search", "Profile", "Library", "AudioInfo", "mor-loader", "Dialogs",

        "mor.stream.scheduler", "mor.tools"

    ]);

    md.controller("MainController", [function () { }]);

    var settings = {
        REST_LOCATION: "http://myownradio.biz/api/v2",
        STREAMS_PER_PAGE: 50
    };

    var routes = {
        /* Home Page */
        PATH_HOME: ["/", {
            templateUrl: "/views/home.html",
            rootClass: "image"
        }],

        /* Streams List */
        PATH_STREAMS_CATALOG: ["/streams/", {
            templateUrl: "/views/streams.html",
            controller: 'ListStreamsController',
            title: "Radio channels on " + SITE_TITLE,
            resolve: {
                channelData: ["$route", "Resolvers", "STREAMS_PER_SCROLL",
                    function ($route, Resolvers, STREAMS_PER_SCROLL) {
                        var category = $route.current.params.category;
                        return Resolvers.getChannelList(null, category, 0, STREAMS_PER_SCROLL);
                    }
                ]
            }
        }],

        PATH_STREAMS_SEARCH: ["/search/:query", {
            templateUrl: "/views/search-results.html",
            controller: "ListSearchController",
            title: "Search results on " + SITE_TITLE,
            resolve: {
                channelData: ["$route", "Resolvers", "STREAMS_PER_SCROLL",
                    function ($route, Resolvers, STREAMS_PER_SCROLL) {
                        var category = $route.current.params.category,
                            filter = $route.current.params.query;
                        return Resolvers.getChannelList(filter, category, 0, STREAMS_PER_SCROLL);
                    }
                ]
            }
        }],

        /* Streams List */
        PATH_STREAMS_BOOKMARKS: ["/bookmarks/", {
            templateUrl: "/views/streams.html",
            controller: 'BookmarksController',
            title: "Your bookmarks on " + SITE_TITLE,
            needsAuth: true,
            resolve: {
                channelData: ["Resolvers", "STREAMS_PER_SCROLL",
                    function (Resolvers, STREAMS_PER_SCROLL) {
                        return Resolvers.getBookmarks(0, STREAMS_PER_SCROLL);
                    }
                ]
            }
        }],

        /* Streams List */
        PATH_STREAMS_USER: ["/user/:key", {
            templateUrl: "/views/streams.html",
            controller: 'UserStreamsController'
        }],

        /* Single Stream View */
        PATH_STREAM: ["/streams/:id", {
            templateUrl: "/views/stream.html",
            controller: "OneStreamController",
            resolve: {
                streamData: ["Streams", "$route", "$document", function (Streams, $route, $document) {
                    var promise = Streams.getByIdWithSimilar($route.current.params.id);
                    promise.then(function (data) {
                        $route.current.title = htmlEscape(data.data.data.stream.name) + " on " + SITE_TITLE;
                    });
                    return promise;
                }]
            }
        }],

        /* Streams Search List */
        PATH_USERS_CATALOG: ["/user", {
            redirectTo: "/"
        }],

        PATH_LOGIN: ["/login/", {
            templateUrl: "/views/login.html",
            controller: "LoginForm"
        }],

        PATH_RECOVER_PASSWORD1: ["/recover", {
            templateUrl: "/views/forms/recoverPassword1.html",
            controller: "PasswordResetBeginForm"
        }],

        PATH_RECOVER_PASSWORD2: ["/recover/:code", {
            templateUrl: "/views/forms/recoverPassword2.html",
            controller: "PasswordResetCompleteForm"
        }],

        PATH_SIGN_UP_BEGIN: ["/signup", {
            templateUrl: "/views/forms/signUpBegin.html",
            controller: "SignUpBeginForm"
        }],

        PATH_SIGN_UP_COMPLETE: ["/signup/:code", {
            templateUrl: "/views/forms/signUpComplete.html",
            controller: "SignUpCompleteForm"
        }],

        PATH_REG_LETTER_SENT: ["/static/registrationLetterSent", {
            templateUrl: "/views/static/registrationLetterSent.html"
        }],

        PATH_REG_COMPLETE: ["/static/registrationCompleted", {
            templateUrl: "/views/static/registrationCompleted.html"
        }],

        PATH_RECOVER_LETTER_SENT: ["/static/resetLetterSent", {
            templateUrl: "/views/static/resetLetterSent.html"
        }],

        PATH_RECOVER_COMPLETED: ["/static/resetPasswordCompleted", {
            templateUrl: "/views/static/resetPasswordCompleted.html"
        }],

        PATH_PROFILE_HOME: ["/profile/", {
            templateUrl: "/views/auth/profile.html",
            title: "Dashboard on " + SITE_TITLE,
            needsAuth: true
        }],

        PATH_PROFILE_EDIT: ["/profile/edit", {
            templateUrl: "/views/auth/editprofile.html",
            title: "Edit profile details on " + SITE_TITLE,
            needsAuth: true
        }],

        PATH_PROFILE_CHANGE_PASSWORD: ["/profile/password", {
            templateUrl: "/views/auth/change-password.html",
            title: "Change password on " + SITE_TITLE,
            needsAuth: true
        }],

        PATH_PROFILE_CHANGE_PLAN: ["/profile/plan", {
            templateUrl: "/views/auth/change-plan.html",
            title: "Change account plan on " + SITE_TITLE,
            needsAuth: true
        }],

        PATH_PROFILE_TRACKS: ["/profile/tracks/", {
            templateUrl: "/views/auth/tracks.html",
            title: "Your tracks on " + SITE_TITLE,
            needsAuth: true
        }],

        PATH_UNUSED_TRACKS: ["/profile/tracks/unused", {
            templateUrl: "/views/auth/tracks.html",
            title: "Your unused tracks on " + SITE_TITLE,
            unused: true,
            needsAuth: true
        }],

        PATH_PROFILE_STREAMS: ["/profile/streams/", {
            templateUrl: "/views/auth/streams.html",
            title: "Your streams on " + SITE_TITLE,
            needsAuth: true
        }],

        PATH_PROFILE_STREAM: ["/profile/streams/:id", {
            templateUrl: "/views/auth/stream.html",
            needsAuth: true
        }],

        PATH_EDIT_STREAM: ["/profile/edit-stream/:id", {
            templateUrl: "/views/auth/edit-stream.html",
            title: "Edit channel details on " + SITE_TITLE,
            needsAuth: true
        }],

        PATH_NEW_STREAM: ["/profile/new-stream", {
            templateUrl: "/views/auth/new-stream.html",
            title: "Create new radio channel on " + SITE_TITLE,
            needsAuth: true
        }],

        PATH_CATEGORIES_LIST: ["/categories/", {
            templateUrl: "/views/categories.html",
            title: "Categories on " + SITE_TITLE
        }]

    };

    md.constant("ROUTES", routes);
    md.constant("SETTINGS", settings);
    md.constant("SITE_TITLE", "MyOwnRadio - Your own web radio station");

    md.config([
        '$routeProvider', '$locationProvider', 'ROUTES', 'cfpLoadingBarProvider',
        '$sceDelegateProvider', '$httpProvider',
        function ($routeProvider, $locationProvider, ROUTES, cfpLoadingBarProvider,
                  $sceDelegateProvider, $httpProvider) {

            cfpLoadingBarProvider.includeSpinner = false;
            $locationProvider.html5Mode(true).hashPrefix('!');
            $sceDelegateProvider.resourceUrlWhitelist([
                'self',
                'http://myownradio.biz:7778/**'
            ]);

            for (key in ROUTES) {
                if (ROUTES.hasOwnProperty(key)) {
                    $routeProvider.when(ROUTES[key][0], ROUTES[key][1])
                }
            }

            /* Otherwise */
            $routeProvider.otherwise({
                redirectTo: "/"
            });

            $httpProvider.defaults.cache = false;


        }]);

    md.run(["$rootScope", "$location", "$route", "$document", "SITE_TITLE", "$analytics", "Response", "$http",

        function ($rootScope, $location, $route, $document, SITE_TITLE, $analytics, Response, $http) {

        $rootScope.lib = {
            countries: [],
            categories: []
        };

        $rootScope.go = function (path) {
            $location.path(path);
        };

        $rootScope.reload = function () {
            Response($http({
                method: "GET",
                url: "/api/v2/getCollection"
            })).onSuccess(function (data) {
                $rootScope.lib = data;
            });
        };

        $rootScope.reload();

        $("a").live("click", function () {
            $analytics.eventTrack('followLink', {category: 'Application', label: this.href});
            if (this.href == $location.absUrl()) {
                $route.reload();
            }
        });

        initHelpers();

        $rootScope.$on("$routeChangeSuccess", function (event, currentRoute) {

            $rootScope.rootClass = currentRoute.rootClass;
            $rootScope.url = $location.url();

            $document.get(0).title = currentRoute.title || SITE_TITLE;

        });

        $rootScope.openedDialogs = 0;

        $rootScope.$on("ngDialog.opened", function () {
            $rootScope.openedDialogs++;
            $rootScope.$apply();
        });

        $rootScope.$on("ngDialog.closed", function () {
            $rootScope.openedDialogs--;
            $rootScope.$apply();
        });


        $rootScope.meta = {
            title: SITE_TITLE,
            image: "",
            url: "",
            description: ""
        };


    }

    ]);

    md.directive("toggle", ["$document", function ($document) {
        return {
            scope: {
                toggle: "="
            },
            link: function ($scope, $element, attrs) {
                $scope.toggle = false;
                $element.on("click", function () {
                    $scope.toggle = !$scope.toggle;
                });
                var callback = function (event) {
                    if (!$element.is(event.target) && $element.find(event.target).length == 0) {
                        $scope.$applyAsync(function () {
                            $scope.toggle = false;
                        });
                    }
                };
                $document.on("click", callback);
                $scope.$on("$destroy", function () {
                    $document.unbind("click", callback);
                });
            }
        }
    }]);


    md.directive('footer', [function () {
        return {
            restrict: 'E',
            templateUrl: '/views/footer.html'
        };
    }]);

    md.directive('tagsList', [function () {
        return {
            restrict: 'A',
            template: '<ul class="taglist">' +
            '<li ng-repeat="tag in tags">' +
            '<a href="/search/%23{{tag | escape}}">{{tag}}</a>' +
            '</li>' +
            '</ul>',
            scope: {
                tags: "=tagsList"
            }
        }
    }]);

    md.directive('header', [function () {
        return {
            restrict: 'E',
            templateUrl: "/views/blocks/header.html"
        }
    }]);

})();
