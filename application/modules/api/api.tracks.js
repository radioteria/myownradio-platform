/**
 * Created by roman on 07.04.15.
 */
(function () {
    var api = angular.module("application");
    api.factory("$tracks", ["$api", function ($api) {
        return {
            library: function (offset, limit) {
                return $api.get("/api/v3/tracks/library", {
                    offset: offset,
                    limit: limit
                });
            },
            channel: function (channel, offset, limit) {
                return $api.get("/api/v3/tracks/channel", {
                    stream_id: channel.sid,
                    offset: offset,
                    limit: limit
                });
            }
        }
    }]);
    api.factory("$likes", ["$api", function ($api) {
        return {
            like: function (track) {
                return $api.post("/api/v3/likes/like", {
                    track_id: track.tid
                });
            },
            dislike: function (track) {
                return $api.post("/api/v3/likes/dislike", {
                    track_id: track.tid
                });
            }
        }
    }]);
    api.factory("$library", ["$api", function ($api) {
        return {
            upload: function (data, callback) {
                return $api.ajaxWrapper($.ajax({
                    xhr: function() {
                        var xhr = new window.XMLHttpRequest();

                        xhr.upload.addEventListener("progress", callback, false);

                        return xhr;
                    },
                    url: "/api/v2/track/upload",
                    type: "POST",
                    data: data,
                    processData: false,
                    contentType: false
                }));
            }
        }
    }]);
})();