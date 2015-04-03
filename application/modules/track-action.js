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

            return {
                deleteStream: function ($stream, callback) {
                    $dialog.question($rootScope.tr("FR_CONFIRM_STREAM_DELETE", [$stream.name]), function () {
                        Streams.deleteStream($stream).onSuccess(function () {
                            if (typeof callback == "function") {
                                callback();
                            }
                        });
                    });
                },
                moveTracksToOtherStream: function (streamObject, tracksArray, streamDestination, successCallback) {
                    $dialog.question($rootScope.pl("FR_MOVE_TRACKS_CONFIRM", tracksArray.length, {tracks:tracksArray, stream:streamDestination}), function () {
                        var trackIds  = tracksArray.map(function (track) { return track.tid; }).join(",");
                        var uniqueIds = tracksArray.map(function (track) { return track.unique_id }).join(",");
                        StreamWorks.addTracks(streamDestination.sid, trackIds).onSuccess(function () {
                            StreamWorks.deleteTracks(streamObject.sid, uniqueIds).onSuccess(function () {
                                if (typeof successCallback == "function") {
                                    successCallback.call();
                                }
                            }, function (message) {
                                Popup.message(message);
                            });
                        }, function (message) {
                            Popup.message(message);
                        });
                    });
                },
                removeTracksFromStream: function (streamObject, tracksArray, successCallback) {
                    $dialog.question($rootScope.pl("FR_DELETE_FROM_STREAM_CONFIRM", tracksArray.length, {tracks:tracksArray, stream:streamObject}, 2), function () {
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
                    $dialog.question($rootScope.pl("FR_DELETE_FROM_ACCOUNT_CONFIRM", tracksArray.length, {tracks:tracksArray}), function () {
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
                },
                copyTrackToSelf: function ($track, successCallback) {
                    TrackWorks.copyTrack($track.tid).onSuccess(function () {
                        Popup.message($rootScope.tr("FR_TRACK_COPIED_SUCCESSFULLY", $track));
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
