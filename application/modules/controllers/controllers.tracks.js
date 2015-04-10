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

    module.directive("likeTrack", ["$likes", function ($likes) {
        return {
            scope: {
                ngModel: "=likeTrack"
            },
            restrict: "A",
            template: "<span class='track-like' ng-if='ngModel'>\
                <span class='dislike'>\
                    <i class='icon-thumbs-o-down hoverable' mor-tooltip='{{ $root.tr(\"DISLIKE_THIS_TRACK\") }}' ng-click='dislike()'></i>\
                    <span class='count' ng-bind='ngModel.dislikes | number' mor-tooltip='{{ $root.pl(\"TRACK_DISLIKED_TIMES\", ngModel.dislikes) }}'></span>\
                </span>\
                <span class='like'>\
                    <i class='icon-thumbs-o-up hoverable' mor-tooltip='{{ $root.tr(\"LIKE_THIS_TRACK\") }}' ng-click='like()'></i>\
                    <span class='count' ng-bind='ngModel.likes | number' mor-tooltip='{{ $root.pl(\"TRACK_LIKED_TIMES\", ngModel.likes) }}'></span>\
                </span>\
            </span>",
            link: function (scope, element, attrs) {
                scope.like = function () {
                    $likes.like(scope.ngModel).then(function () {
                        scope.ngModel.likes += 1;
                    });
                };
                scope.dislike = function () {
                    $likes.dislike(scope.ngModel).then(function () {
                        scope.ngModel.dislikes += 1;
                    });
                };
            }
        }
    }]);

})();