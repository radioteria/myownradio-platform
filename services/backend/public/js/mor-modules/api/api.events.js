
(function () {

    var api = angular.module("application");

    api.run(["$timeout", "$event_watcher", "$rootScope", function ($timeout, $event_watcher, $rootScope) {
        var listener = null;
        var watch = function () {
            $event_watcher.watch(listener.hash)
                .then(function (data) {
                    if (angular.isArray(data)) {
                        for (var i = 0; i < data.length; i += 1) {
                            $rootScope.$broadcast("event", data[i]);
                        }
                    } else if (angular.isObject(data)) {
                        $rootScope.$broadcast("event", data);
                    }
                    watch();
                })
                .catch(function (data) {
                    $timeout(5000, login);
                })
        };
        var login = function () {
            $event_watcher.login().then(function (data) {
                listener = data;
                watch();
            }).catch(function (data) {
                $timeout(5000, login);
            });
        };
        login();
    }]);

    api.service("$event_watcher", ["$http", function($http) {
        var settings = {
            ignoreLoadingBar: true
        };
        return {
            login: function () {
                return $http.get(location.origin + ":8443/notif1er/hello?app=mor&keys=*", settings)
                    .then(function (data) { return data.data; });
            },
            watch: function (hash) {
                return $http.get(location.origin + ":8443/notif1er/watch?hash=" + hash, settings)
                    .then(function (data) { return data.data; });
            }
        }
    }])

})();