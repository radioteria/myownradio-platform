/**
 * Created by roman on 05.04.15.
 */
(function() {

  var app = angular.module("application");

  app.factory("$categories", ["$http", "Response", function ($http, Response) {
    return {
      list: function () {
        var action = $http({
          method: "GET",
          url: "/api/v2/categories"
        });
        return Response(action);
      }
    }
  }]);

})();
