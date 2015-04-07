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
                    show: function (source) {
                        var scope = $rootScope.$new();
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

                var keys = [
                    { name: "title",        default: "" },
                    { name: "artist",       default: "" },
                    { name: "album",        default: "" },
                    { name: "track_number", default: "" },
                    { name: "genre",        default: "" },
                    { name: "date",         default: "" },
                    { name: "color_id",     default: "" },
                    { name: "cue",          default: "" },
                    { name: "buy",          default: ""},
                    { name: "can_be_shared",default: 0 }
                ];

                var metadata = {}, i, j, length;

                for (i = 0; i < keys.length; i ++) {
                    metadata[keys[i].name] = key[i].default;
                    metadata["save_" + keys[i].name] = true;
                }

                $scope.metadata = metadata;

                $scope.save = function () {

                    var data = {
                        track_id: tracks
                    };

                    for (i = 0; i < keys.length; i ++) {
                        if ($scope.metadata["save_" + keys[i].name]) {
                            data[keys[i].name] = $scope.metadata[keys[i].name];
                        }
                    }

                    AudioInfoEditor.save(data).onSuccess(function () {
                        for (i = 0, length = $scope.source.length; i < length; i += 1) {

                            for (j = 0; j < keys.length; j ++) {
                                if ($scope.metadata["save_" + keys[j].name]) {
                                    data[keys[j].name] = metadata[keys[j].name];
                                    $scope.source[i][keys[j].name] = $scope.metadata[keys[j].name];
                                }
                            }

                        }
                        $scope.closeThisDialog();
                    }, function (error) {
                        $dialog.message(error);
                    });

                };

                for (i = 0, length = $scope.source.length; i < length; i += 1) {
                    if (i == 0) {

                        for (j = 0; j < keys.length; j ++) {
                            $scope.metadata[keys[j].name] = $scope.source[i][keys[j].name];
                        }

                    } else {

                        for (j = 0; j < keys.length; j ++) {

                            if ($scope.metadata[keys[j].name] != $scope.source[i][keys[j].name]) {
                                $scope.metadata["save_" + keys[j].name] = false;
                                $scope.metadata[keys[j].name] = keys[j].default;
                            }

                        }
                    }
                }

            }
        ])

})();