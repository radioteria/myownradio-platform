(function () {
angular.module("Dialogs", [])
    .factory("TrackAction", [
        "TrackWorks",
        "StreamWorks",
        "Streams",
        "$dialog",
        "Popup",
        "$rootScope",
        function (TrackWorks, StreamWorks, Streams, $dialog, Popup, $rootScope) {

            var getFileName = function (array) {
                return array.length == 1 ?
                    "track <b>" + array[0].filename + "</b>" :
                    "<b>" + array.length.toString() + " track(s)</b>";
            };

            return {
                deleteStream: function ($stream) {
                    $dialog.question("Are you sure want to delete radio channel <b>" + $stream.name + "</b>?", function () {
                        Streams.deleteStream($stream).onSuccess(function () {
                            $rootScope.account.init("/profile/streams/");
                        });
                    });
                },
                removeTracksFromStream: function (streamObject, tracksArray, successCallback) {
                    $dialog.question("Delete " + getFileName(tracksArray) + " from stream?", function () {
                        var trackIds = tracksArray.map(function (track) { return track.unique_id }).join(",");
                        StreamWorks.deleteTracks(streamObject.sid, trackIds).onSuccess(function () {
                            if (typeof successCallback == "function") {
                                successCallback.call();
                            }
                        });
                    }, function (message) {
                        Popup.message(message);
                    });
                },
                removeTracksFromAccount: function (tracksArray, successCallback) {
                    $dialog.question("Delete " + getFileName(tracksArray) +  " from your account?", function () {
                        var trackIds = tracksArray.map(function (track) { return track.tid; }).join(",");
                        TrackWorks.deleteTracks(trackIds).onSuccess(function () {
                            if (typeof successCallback == "function") {
                                successCallback.call();
                            }
                        });
                    }, function (message) {
                        Popup.message(message);
                    });
                },
                addTracksToStream: function (streamObject, tracksArray, successCallback) {
                    var trackIds = tracksArray.map(function (track) { return track.tid; }).join(",");
                    StreamWorks.addTracks(streamObject.sid, trackIds).onSuccess(function () {
                        if (typeof successCallback == "function") {
                            successCallback.call();
                        }
                    }, function (message) {
                        Popup.message(message);
                    });
                },
                changeTracksColor: function (colorObject, tracksArray, successCallback) {
                    var trackIds = tracksArray.map(function (track) { return track.tid; }).join(",");
                    TrackWorks.updateColor(trackIds, colorObject.color_id).onSuccess(function () {
                        if (typeof successCallback == "function") {
                            successCallback.call();
                        }
                    }, function (message) {
                        Popup.message(message);
                    });
                }
            };
        }
    ]);
})();
