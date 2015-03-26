(function () {

    angular.module("AudioInfo", ["Site", "ngDialog"])

        .factory("AudioInfoEditor", ["$rootScope", "$http", "Response", "ngDialog",

            function ($rootScope, $http, Response, ngDialog) {

                return {
                    save: function (metadata) {
                        return Response($http({
                            method: "POST",
                            url: "/api/v2/track/edit",
                            data: metadata
                        }))
                    },
                    show: function (source, $scope) {
                        var scope = $scope.$new();
                        scope.source = source;
                        ngDialog.open({
                            templateUrl: "/views/auth/metadata.html",
                            controller: "TrackInfoController",
                            scope: scope,
                            showClose: false
                        });
                    }
                }

            }

        ])

        .controller("TrackInfoController", ["$scope", "AudioInfoEditor", "$dialog",

            function ($scope, AudioInfoEditor, $dialog) {

                var tracks = $scope.source.map(function (o) { return o.tid; }).join(",");

                $scope.metadata = {
                    title:          "",     saveTitle: true,
                    artist:         "",     saveArtist: true,
                    album:          "",     saveAlbum: true,
                    trackNumber:    "",     saveTrackNumber: true,
                    genre:          "",     saveGenre: true,
                    date:           "",     saveDate: true,
                    colorId:        "",     saveColorId: true,
                    cue:            "",     saveCue: true,
                    buy:            "",     saveBuy: true
                };

                $scope.save = function () {

                    var data = {
                        track_id: tracks
                    };

                    if ($scope.metadata.saveTitle)
                        data.title = $scope.metadata.title;
                    if ($scope.metadata.saveArtist)
                        data.artist = $scope.metadata.artist;
                    if ($scope.metadata.saveAlbum)
                        data.album = $scope.metadata.album;
                    if ($scope.metadata.saveTrackNumber)
                        data.track_number = $scope.metadata.trackNumber;
                    if ($scope.metadata.saveGenre)
                        data.genre = $scope.metadata.genre;
                    if ($scope.metadata.saveDate)
                        data.date = $scope.metadata.date;
                    if ($scope.metadata.saveColorId)
                        data.color_id = $scope.metadata.colorId;
                    if ($scope.metadata.saveCue)
                        data.cue = $scope.metadata.cue;
                    if ($scope.metadata.saveBuy)
                        data.buy = $scope.metadata.buy;

                    AudioInfoEditor.save(data).onSuccess(function () {
                        for (var i = 0, length = $scope.source.length; i < length; i += 1) {
                            if ($scope.metadata.saveTitle)
                                $scope.source[i].title = $scope.metadata.title;
                            if ($scope.metadata.saveArtist)
                                $scope.source[i].artist = $scope.metadata.artist;
                            if ($scope.metadata.saveAlbum)
                                $scope.source[i].album = $scope.metadata.album;
                            if ($scope.metadata.saveTrackNumber)
                                $scope.source[i].track_number = $scope.metadata.trackNumber;
                            if ($scope.metadata.saveGenre)
                                $scope.source[i].genre = $scope.metadata.genre;
                            if ($scope.metadata.saveDate)
                                $scope.source[i].date = $scope.metadata.date;
                            if ($scope.metadata.saveColorId)
                                $scope.source[i].color_id = $scope.metadata.colorId;
                            if ($scope.metadata.saveCue)
                                $scope.source[i].cue = $scope.metadata.cue;
                            if ($scope.metadata.saveBuy)
                                $scope.source[i].buy = $scope.metadata.buy;
                        }
                        $scope.closeThisDialog();
                    }, function (error) {
                        $dialog.info(error);
                    });

                };

                for (var i = 0, length = $scope.source.length; i < length; i += 1) {
                    if (i == 0) {
                        $scope.metadata.title       = $scope.source[i].title;
                        $scope.metadata.artist      = $scope.source[i].artist;
                        $scope.metadata.album       = $scope.source[i].album;
                        $scope.metadata.trackNumber = $scope.source[i].track_number;
                        $scope.metadata.genre       = $scope.source[i].genre;
                        $scope.metadata.date        = $scope.source[i].date;
                        $scope.metadata.colorId     = $scope.source[i].color;
                        $scope.metadata.cue         = $scope.source[i].cue;
                        $scope.metadata.buy         = $scope.source[i].buy;
                    } else {
                        if ($scope.metadata.title != $scope.source[i].title) {
                            $scope.metadata.saveTitle = false;
                            $scope.metadata.title = ""
                        }
                        if ($scope.metadata.artist != $scope.source[i].artist) {
                            $scope.metadata.saveArtist = false;
                            $scope.metadata.artist = ""
                        }
                        if ($scope.metadata.album != $scope.source[i].album) {
                            $scope.metadata.saveAlbum = false;
                            $scope.metadata.album = ""
                        }
                        if ($scope.metadata.trackNumber != $scope.source[i].track_number) {
                            $scope.metadata.saveTrackNumber = false;
                            $scope.metadata.trackNumber = ""
                        }
                        if ($scope.metadata.genre != $scope.source[i].genre) {
                            $scope.metadata.saveGenre = false;
                            $scope.metadata.genre = ""
                        }
                        if ($scope.metadata.date != $scope.source[i].date) {
                            $scope.metadata.saveDate = false;
                            $scope.metadata.date = ""
                        }
                        if ($scope.metadata.colorId != $scope.source[i].color) {
                            $scope.metadata.saveColorId = false;
                            $scope.metadata.colorId = ""
                        }
                        if ($scope.metadata.cue != $scope.source[i].cue) {
                            $scope.metadata.saveCue = false;
                            $scope.metadata.cue = ""
                        }
                        if ($scope.metadata.buy != $scope.source[i].buy) {
                            $scope.metadata.saveBuy = false;
                            $scope.metadata.buy = ""
                        }
                    }
                }

            }
        ])

})();