/**
 * Created by Roman on 06.04.15.
 */
(function () {

    var module = angular.module("application");

    module.service("$api", ["$q", "$http", function ($q, $http) {

        function answer(promise) {
            return $q(function (resolve, reject) {
                promise.then(function (response) {
                    if (response.data.code == 1) {
                        resolve(response.data.data);
                    } else {
                        reject(response.data.message);
                    }
                })
            });
        }

        return {
            get: function (url, params) {
                return answer($http.get(url, {
                    params: params
                }));
            },
            put: function (url, params) {
                return answer($http.put(url, null, {
                    params: params
                }));
            },
            delete: function (url, params) {
                return answer($http.delete(url, {
                    params: params
                }));
            },
            post: function (url, post) {
                return answer($http.post(url, post));
            },
            filter: function (arguments) {
                var obj = {};
                for (var key in arguments) if (arguments.hasOwnProperty(key)) {
                    if (typeof arguments[key] != "undefined") {
                        obj[key] = arguments[key];
                    }
                }
                return obj;
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
                        data != scope.synchronize &&
                        data[scope.syncKey] == scope.synchronize[scope.syncKey]) {

                        angular.copy(data, scope.synchronize);

                    }
                });
                scope.$watchCollection("synchronize", function () {
                    $rootScope.$broadcast("sync:update:" + scope.syncKey, scope.synchronize);
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