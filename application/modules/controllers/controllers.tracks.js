/**
 * Created by roman on 07.04.15.
 */
(function () {

    var module = angular.module("application");

    module.factory("TrackListActions", ["AudioInfoEditor", "TrackAction", function (AudioInfoEditor, TrackAction) {
        return {
            editTrack: function (tracks) {
                AudioInfoEditor.show(tracks);
            },
            copyTrack: function (track) {
                TrackAction.copyTrackToSelf(track);
            }
        }
    }]);

})();