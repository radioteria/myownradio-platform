
(function () {

    var api = angular.module("application");

    api.run(["$timeout", "$event_watcher", "$rootScope", function ($timeout, $event_watcher, $rootScope) {
        var watch = function () {
            $event_watcher.watch()
                .then(function (data) {
                    console.log(data);
                    watch();
                })
                .catch(function (data) {
                    console.log(data);
                    $timeout(5000, watch);
                })
        };
        watch();
    }]);

    api.service("$event_watcher", ["$http", function($http) {
        return {
            watch: function () {
                return $http.get("http://myownradio.biz:8080/watch?app=mor&keys=*");
            }
        }
    }])

})();