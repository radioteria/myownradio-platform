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

    module.directive("likeTrack", ["$likes", "Popup", function ($likes, Popup) {
        return {
            scope: {
                ngModel: "=likeTrack"
            },
            restrict: "A",
            template: "<div class='track-like overflow-hidden' ng-if='ngModel'>\
                <div class='dislike left'>\
                    <i class='icon-thumbs-o-down hoverable' mor-tooltip='{{ $root.tr(\"DISLIKE_THIS_TRACK\") }}' ng-click='dislike()'></i>\
                    <span class='count' ng-bind='ngModel.dislikes | number' mor-tooltip='{{ $root.pl(\"TRACK_DISLIKED_TIMES\", ngModel.dislikes) }}'></span>\
                </div>\
                <div class='like-meter left' like-meter='ngModel'></div>\
                <div class='like left'>\
                    <i class='icon-thumbs-o-up hoverable' mor-tooltip='{{ $root.tr(\"LIKE_THIS_TRACK\") }}' ng-click='like()'></i>\
                    <span class='count' ng-bind='ngModel.likes | number' mor-tooltip='{{ $root.pl(\"TRACK_LIKED_TIMES\", ngModel.likes) }}'></span>\
                </div>\
            </div>",
            link: function (scope, element, attrs) {
                scope.like = function () {
                    $likes.like(scope.ngModel).then(function (data) {
                        scope.ngModel.likes = data.likes;
                        scope.ngModel.dislikes = data.dislikes;
                    }, function (message) {
                        Popup.message(message);
                    });
                };
                scope.dislike = function () {
                    $likes.dislike(scope.ngModel).then(function (data) {
                        scope.ngModel.likes = data.likes;
                        scope.ngModel.dislikes = data.dislikes;
                    }, function (message) {
                        Popup.message(message);
                    });
                };
            }
        }
    }]);

    module.directive("likeMeter", [function () {
        return {
            scope: { ngModel: "=likeMeter" },
            restrict: "A",
            template: "<div class='wrap'><div class='dislikes left'></div><div class='likes left'></div></div>",
            link: function (scope, element, attrs) {

                var $like = element.find(".likes"),
                    $dislike = element.find(".dislikes");

                scope.$watchGroup(["ngModel.likes", "ngModel.dislikes"], function () {
                    var sum = scope.ngModel.likes + scope.ngModel.dislikes;
                    if (sum == 0) {
                        $like.css("width", 0);
                        $dislike.css("width", 0);
                    } else {
                        $like.css("width", (100 / sum * scope.ngModel.likes).toString().concat("%"));
                        $dislike.css("width", (100 / sum * scope.ngModel.dislikes).toString().concat("%"));
                    }
                });

            }
        }
    }]);

})();