(function () {

    var app = angular.module("application");

    app.filter("escape", function () {
        return window.encodeURI;
    });

    app.filter("usersCatalog", ['ROUTES', function (ROUTES) {
        return function (key) {
            return ROUTES.PATH_USERS_CATALOG[0] + key;
        }
    }])

})();
