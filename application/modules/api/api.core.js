/**
 * Created by Roman on 06.04.15.
 */
(function () {

    var module = angular.module("application");

    module.service("$api", ["$q", "$http", function ($q, $http) {

        function answer(promise) {

            var deferred = $q.defer();

            promise.then(function (response) {
                if (response.data.code == 1) {
                    deferred.resolve(response.data.data);
                } else {
                    deferred.reject(response.data.message);
                }
            }, function (event, request) {
                deferred.reject(request);
            });

            deferred.promise.abort = promise.abort;

            return deferred.promise;

        }

        function ajaxAnswer(promise) {

            var deferred = $q.defer();

            promise.then(function (response) {
                if (response.code == 1) {
                    deferred.resolve(response.data);
                } else {
                    deferred.reject(response.message);
                }
            }, function (event, request) {
                deferred.reject(request);
            });

            deferred.promise.abort = promise.abort;

            return deferred.promise;

        }

        return {
            get: function (url, params) {
                return answer($http.get(url, {
                    cache: false,
                    params: params
                }));
            },
            put: function (url, params) {
                return answer($http.put(url, null, {
                    cache: false,
                    params: params
                }));
            },
            delete: function (url, params) {
                return answer($http.delete(url, {
                    cache: false,
                    params: params
                }));
            },
            post: function (url, post) {
                return answer($http.post(url, post, {
                    cache: false
                }));
            },
            filter: function (arguments) {
                var obj = {};
                for (var key in arguments) if (arguments.hasOwnProperty(key)) {
                    if (typeof arguments[key] != "undefined") {
                        obj[key] = arguments[key];
                    }
                }
                return obj;
            },
            wrapper: function (promise) {
                return answer(promise);
            },
            ajaxWrapper: function (promise) {
                return ajaxAnswer(promise);
            }
        }

    }]);

    module.directive("synchronize", ["$rootScope", function ($rootScope) {
        return {
            scope: {
                synchronize: "=",
                syncKey: "@"
            },
            restrict: "A",
            link: function (scope, element, attrs) {
                scope.$on("sync:update:" + scope.syncKey, function (event, data) {
                    if (angular.isObject(scope.synchronize) &&
                        data !== scope.synchronize &&
                        data[scope.syncKey] === scope.synchronize[scope.syncKey]) {

                        angular.copy(data, scope.synchronize);

                    }
                });
                scope.$watchCollection("synchronize", function (value) {
                    if (angular.isObject(value)) {
                        $rootScope.$broadcast("sync:update:" + scope.syncKey, value);
                    }
                });
            }
        }
    }]);

    module.directive("copy", [function () {
        return {
            restrict: "E",
            scope: {
                source: "=",
                destination: "="
            },
//            require: "source|destination",
            link: function (scope, element, attrs) {
                scope.$watchCollection(scope.source, function (changes) {
                    angular.copy(changes, scope.destination);
                });
            }
        }
    }])

})();