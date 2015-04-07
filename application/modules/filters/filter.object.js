/**
 * Created by Roman on 07.04.15.
 */
(function () {
    var module = angular.module("application");

    var paths = {
        user: "/user/",
        stream: "/stream/"
    };

    module.filter("userProfileLink", [function () {
        return function ($user) {
            return paths.user + ($user.permalink || $user.uid);
        }
    }]);

    module.filter("userProfileName", [function () {
        return function ($user) {
            return $user.name || $user.login;
        }
    }]);

    module.filter("channelKey", [function () {
        return function ($channel) {
            return $channel.permalink || $channel.sid;
        }
    }]);

})();