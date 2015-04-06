/**
 * Created by Roman on 06.04.15.
 */
(function () {
    var module = angular.module("application");

    module.factory("$bookmarks", ["$api", function ($api) {
        return {
            addBookmark: function (channel) {
                return $api.put("/api/v2/bookmark", {
                    stream_id: channel.sid
                });
            },
            removeBookmark: function (channel) {
                return $api.delete("/api/v2/bookmark", {
                    stream_id: channel.sid
                });
            }
        }
    }])
})();
