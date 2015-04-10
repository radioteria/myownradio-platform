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
                ngModel: "="
            },
            restrict: "A",
            template: "<span class='track-like'>\
                <span class='dislike' ng-click='dislike()'>\
                    <i class='icon-thumbs-o-down'></i>\
                    <span ng-bind='ngModel.dislikes | number'></span>\
                </span>\
                <span class='like' ng-click='dislike()'>\
                    <i class='icon-thumbs-o-up'></i>\
                    <span ng-bind='ngModel.likes | number'></span>\
                </span>\
            </span>",
            link: function (scope, element, attrs) {
                scope.like = function () {
                    $likes.like(scope.ngModel).then(function () {
                        scope.ngModel.likes_count ++;
                    });
                };
                scope.dislike = function () {
                    $likes.dislike(scope.ngModel).then(function () {
                        scope.ngModel.dislikes_count ++;
                    });
                };
            }
        }
    }]);

})();