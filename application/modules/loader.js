(function () {
    angular.module("mor-loader", ["Site"])
        .config([
            '$httpProvider',
            function ($httpProvider) {

                var interceptor = [
                    '$q',
                    '$rootScope',
                    '$timeout',
                    function ($q, $rootScope, $timeout) {

                        var requestsTotal = 0,
                            requestsCompleted = 0;

                        var timeoutHandle;

                        var action = {
                            startBusy: function () {
                                $timeout.cancel(timeoutHandle);
                                timeoutHandle = $timeout(function () {
                                    $rootScope.loader.busy = true;
                                }, 200);
                            },
                            completeBusy: function () {
                                $timeout.cancel(timeoutHandle);
                                $rootScope.loader.busy = false;
                            }
                        };


                        return {
                            'request': function(config) {
                                if (config.busy === true) {
                                    requestsTotal ++;
                                }
                                if (requestsCompleted < requestsTotal) {
                                    action.startBusy();
                                }
                                return config;
                            },

                            'response': function(response) {
                                if (response.config.busy === true) {
                                    requestsCompleted ++;
                                }
                                if (requestsCompleted == requestsTotal) {
                                    action.completeBusy();
                                }
                                return response;
                            },

                            'responseError': function(rejection) {
                                if (rejection.config.busy === true) {
                                    requestsCompleted ++;
                                }
                                if (requestsCompleted == requestsTotal) {
                                    action.completeBusy();
                                }
                                return $q.reject(rejection);
                            }
                        };
                    }
                ];
                $httpProvider.interceptors.push(interceptor);
            }
        ])
        .run([
            '$rootScope',
            function ($rootScope) {
                $rootScope.loader = { busy: false }
            }
        ]);
})();