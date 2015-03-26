/**
 * Module Catalog
 */
(function () {

    var catalog = angular.module("Catalog", ["Site"]);

    catalog.constant("STREAMS_PER_SCROLL", 20);
    catalog.constant("TIMELINE_RESOLUTION", 1800000);


    catalog.controller("StreamEditorController", ["$scope", "$rootScope", "$routeParams", "Streams", "$location", "$dialog",

        function ($scope, $rootScope, $routeParams, Streams, $location, $dialog) {

            var id = $routeParams.id;
            $scope.error = "";
            $scope.stream = {};
            $scope.init = function () {
                Streams.getByID(id).onSuccess(function (response) {
                    $scope.stream = response;
                }, function () {
                    $location.url("/profile/streams/");
                });
            };
            $scope.submit = function () {
                $scope.error = "";
                Streams.updateStreamInfo($scope.stream).onSuccess(function () {
                    $rootScope.reload();
                    $rootScope.account.init("/profile/streams/" + $scope.stream.sid);
                }, function (err) {
                    $scope.error = err;
                });
            };

            $scope.init();

        }

    ]);

    catalog.controller("NewStreamController", ["$scope", "Streams", "$rootScope", "Response", "$http",

        function ($scope, Streams, $rootScope, Response, $http) {
            $scope.status = "";
            $scope.error = "";
            $scope.stream = {
                name: "",
                info: "",
                hashtags: "",
                permalink: "",
                category: 0,
                access: "PUBLIC"
            };

            $scope.cover = null;
            $scope.preview = null;

            $scope.submit = function () {
                $scope.status = "";
                $scope.error = "";

                var fd = new FormData();

                if ($scope.cover) {
                    fd.append("file", $scope.cover);
                }

                fd.append("name", $scope.stream.name);
                fd.append("info", $scope.stream.info);
                fd.append("tags", $scope.stream.hashtags);
                fd.append("permalink", $scope.stream.permalink);
                fd.append("category", $scope.stream.category);
                fd.append("access", $scope.stream.access);

                var uploader = Response($http({
                    method: "POST",
                    url: "/api/v2/stream/create",
                    data: fd,
                    transformRequest: angular.identity,
                    headers: {'Content-Type': undefined}
                }));

                uploader.onSuccess(function (data) {
                    $rootScope.reload();
                    $rootScope.account.init("/profile/streams/" + data.sid);
                }, function (resp) {
                    $scope.error = resp;
                });

            };

        }

    ]);

    catalog.controller("StreamCoverController", ["$scope", "$http", "Response", "$routeParams",

        function ($scope, $http, Response, $routeParams) {

            var id = $routeParams.id;
            $scope.upload = function () {

                var file = $("<input>");

                file.attr("type", "file");
                file.attr("accept", "image/jpeg,image/png")
                file.on("change", function (event) {
                    if (this.files.length == 0) return;

                    var fd = new FormData();
                    fd.append("file", this.files[0]);
                    fd.append("stream_id", id);

                    var uploader = Response($http({
                        method: "POST",
                        url: "/api/v2/stream/changeCover",
                        data: fd,
                        transformRequest: angular.identity,
                        headers: {'Content-Type': undefined}
                    }));

                    uploader.onSuccess(function (response) {
                        $scope.stream.cover_url = response.url;
                        $scope.stream.cover = response.name;
                    });

                });
                file.click();
            };

            $scope.remove = function () {

                var uploader = Response($http({
                    method: "POST",
                    url: "/api/v2/stream/removeCover",
                    data: {stream_id: id}
                }));

                uploader.onSuccess(function () {
                    $scope.stream.cover_url = null;
                    $scope.stream.cover = null;
                });

            };
        }

    ]);

    catalog.controller("NewStreamCoverController", ["$scope", function ($scope) {

        var reader = new FileReader();

        reader.onload = function (e) {
            $scope.$parent.preview = e.target.result;
            $scope.$digest();
        };


        $scope.upload = function () {

            var file = $("<input>");

            file.attr("type", "file");
            file.attr("accept", "image/jpeg,image/png");
            file.on("change", function () {
                if (this.files.length == 0) return;
                $scope.$parent.cover = this.files[0];
                reader.readAsDataURL(this.files[0]);
            });
            file.click();
        };

        $scope.remove = function () {

            $scope.$parent.cover = null;
            $scope.$parent.preview = null;

        };

    }]);

    catalog.controller("SearchFormController", ["$element", "$scope", "$location", "Streams", "$document",

        function ($element, $scope, $location, Streams, $document) {
            $scope.filter = "";
            $scope.streams = [];
            $scope.focused = false;

            $document.bind("click", function (event) {
                $scope.$apply(function () {
                    $scope.focused = ($element.find(event.target).length > 0);
                });
            });

            $element.on("keyup", function (event) {
                $scope.$apply(function () {
                    $scope.focused = true;
                });
            });

            $scope.goSearch = function () {
                $location.url("/search/".concat(encodeURIComponent($scope.filter)));
                $scope.filter = "";
            };
            $scope.goStream = function (key) {
                $location.url("/streams/".concat(key));
                $scope.filter = "";
            };
            $scope.$watch("filter", function (value) {
                if (typeof value == "string" && value.length > 0) {
                    Streams.getList($scope.filter, "", 0, 5).onSuccess(function (data) {
                        $scope.streams = data.streams;
                    });
                } else {
                    $scope.streams = [];
                }
            });
        }

    ]);


    catalog.controller("ListStreamsController", ["$scope", "$location", "Streams", "STREAMS_PER_SCROLL", "channelData",

        function ($scope, $location, Streams, STREAMS_PER_SCROLL, channelData) {

            var category = $location.search().category;

            $scope.content = {
                streams: [],
                empty: false
            };

            channelData.onSuccess(function (data) {
                $scope.content.streams = data.streams;
                $scope.content.empty = data.streams.length > 0;
            });

            $scope.busy = false;

            $scope.load = function () {
                $scope.busy = true;
                Streams.getList(null, category, $scope.content.streams.length, STREAMS_PER_SCROLL, false)
                    .onSuccess(function (response) {
                        $scope.content.streams = $scope.content.streams.concat(response.streams);
                        if (response.streams.length > 0) {
                            $scope.busy = false;
                        }
                        if ($scope.content.streams.length == 0) {
                            $scope.content.empty = true;
                        }
                    });
            };

        }

    ]);

    catalog.controller("ListSearchController", ["$scope", "$location", "$routeParams", "Streams", "STREAMS_PER_SCROLL", "channelData",

        function ($scope, $location, $routeParams, Streams, STREAMS_PER_SCROLL, channelData) {

            var category = $location.search().category;

            $scope.query = decodeURI($routeParams.query);

            $scope.content = {
                streams: [],
                empty: false
            };

            channelData.onSuccess(function (data) {
                $scope.content.streams = data.streams;
                $scope.content.empty = data.streams.length > 0;
            });

            $scope.busy = false;

            $scope.load = function () {
                $scope.busy = true;
                Streams.getList($scope.query, category, $scope.content.streams.length, STREAMS_PER_SCROLL, $scope.content.streams.length === 0)
                    .onSuccess(function (response) {
                        $scope.content.streams = $scope.content.streams.concat(response.streams);
                        if (response.streams.length > 0) {
                            $scope.busy = false;
                        }
                        if ($scope.content.streams.length == 0) {
                            $scope.content.empty = true;
                        }
                    });
            };

        }

    ]);

    catalog.controller("BookmarksController", ["$scope", "$location", "Streams", "STREAMS_PER_SCROLL", "channelData",

        function ($scope, $location, Streams, STREAMS_PER_SCROLL, channelData) {
            $scope.content = {
                streams: [],
                empty: false
            };

            channelData.onSuccess(function (data) {
                $scope.content.streams = data;
                $scope.content.empty = data.length > 0;
            });

            $scope.busy = false;

            $scope.load = function () {
                $scope.busy = true;
                Streams.getBookmarks($scope.content.streams.length, $scope.content.streams.length === 0)
                    .onSuccess(function (response) {
                        $scope.content.streams = $scope.content.streams.concat(response);
                        if (response.length > 0) {
                            $scope.busy = false;
                        }
                        if ($scope.content.streams.length == 0) {
                            $scope.content.empty = true;
                        }
                    });
            };

        }

    ]);

    catalog.controller("UserStreamsController", ["$scope", "$routeParams", "Streams", "STREAMS_PER_SCROLL",

        function ($scope, $routeParams, Streams, STREAMS_PER_SCROLL) {
            $scope.user = $routeParams.key;
            $scope.content = {
                streams: [],
                owner: null,
                loaded: false,
                empty: false
            };
            $scope.busy = false;
            $scope.load = function () {
                $scope.busy = true;
                Streams.getByUser($scope.user, $scope.content.streams.length, $scope.content.streams.length === 0)
                    .onSuccess(function (response) {
                        $scope.content.streams = $scope.content.streams.concat(response.streams);
                        $scope.content.owner = response.user;
                        $scope.htmlReady();
                        if (response.streams.length == STREAMS_PER_SCROLL) {
                            $scope.busy = false;
                        }
                        $scope.content.loaded = true;
                        if ($scope.content.streams.length == 0) {
                            $scope.content.empty = true;
                        }
                    }, function (err) {
                        console.log(err);
                        $scope.busy = false;
                    });
            };

        }

    ]);

    catalog.controller("MyStreamsController", ["$rootScope", "$scope", "$dialog", "Streams", "StreamWorks",

        function ($rootScope, $scope, $dialog, Streams, StreamWorks) {

            $scope.deleteStream = function (stream) {
                $dialog.question("Are you sure want to delete stream<br><b>" + htmlEscape(stream.name) + "</b>?", function () {
                    Streams.deleteStream(stream).onSuccess(function () {
                        deleteMatching($rootScope.account.streams, function (obj) {
                            return stream.sid == obj.sid
                        });
                        $rootScope.account.user.streams_count = $rootScope.account.user.streams_count - 1;
                    });
                });
            };

            $scope.changeStreamState = function (stream) {
                if (stream.status == 0) {
                    StreamWorks.startStream(stream).onSuccess(function () {
                        $rootScope.account.init();
                    });
                } else {
                    $dialog.question("Are you sure want to <b>shut down</b> stream<br><b>" + htmlEscape(stream.name) + "</b>?", function () {
                        StreamWorks.stopStream(stream).onSuccess(function () {
                            $rootScope.account.init();
                        });
                    });
                }
            }

        }

    ]);

    catalog.controller("OneStreamController", ["$rootScope", "$scope", "$location", "Streams", "$routeParams",
        "$document", "SITE_TITLE", "AudioInfoEditor", "TrackAction", "StreamWorks", "streamData",

        function ($rootScope, $scope, $location, Streams, $routeParams, $document, SITE_TITLE, AudioInfoEditor, TrackAction, StreamWorks, streamData) {
            $scope.content = {
                id: $routeParams.id,
                streamData: null,
                similarStreams: null
            };
            if (streamData.data.code == 1) {
                $scope.content.streamData = streamData.data.data.stream;
                $scope.content.similarStreams = streamData.data.data.similar.streams;
                $document.get(0).title = htmlEscape(streamData.data.data.stream.name) + " on " + SITE_TITLE;
            } else {
                $location.url("/streams/");
            }
            $scope.play = function () {
                $scope.content.streamData.listeners_count += 1;
                $rootScope.player.controls.loadStream($scope.content.streamData);
            };
            $scope.stop = function () {
                $scope.content.streamData.listeners_count = Math.max($scope.content.streamData.listeners_count - 1, 0);
                $rootScope.player.controls.stop();
            };
            $scope.toggle = function () {
                (($rootScope.player.isPlaying == true && $rootScope.player.currentID == $scope.content.streamData.sid) ? $scope.stop : $scope.play)();
            };
            $scope.$on("BOOKMARK", function (obj, data) {
                if (data.id == $scope.content.streamData.sid) {
                    if (data.bookmarked == true) {
                        $scope.content.streamData.bookmarks_count++;
                    } else {
                        $scope.content.streamData.bookmarks_count--;
                    }
                }
            });
            $scope.edit = function () {
                AudioInfoEditor.show([$rootScope.player.nowPlaying], $scope);
            };
            $scope.editTrack = function (track) {
                AudioInfoEditor.show([track], $scope);
            };
            $scope.remove = function () {
                TrackAction.removeTracksFromStream($scope.content.streamData, [$rootScope.player.nowPlaying]);
            };
            $scope.removeTrack = function (track) {
                TrackAction.removeTracksFromStream($scope.content.streamData, [track]);
            };

            $scope.$watch("scheduleTracks", function (data) {
                if (data !== null && $scope.content.streamData !== null) {
                    $scope.content.streamData.listeners_count = data.listeners_count;
                    $scope.content.streamData.bookmarks_count = data.bookmarks_count;
                }
            });

            $scope.sort = function (uniqueId, newIndex) {
                StreamWorks.sort($scope.content.streamData.sid, uniqueId, newIndex + 1).onSuccess(function () {

                });
            };

            $scope.sortableOptions = {
                axis: 'y',
                items: ".track-row",
                stop: function (event, ui) {
                    var thisElement = angular.element(ui.item).scope();
                    var thisIndex = angular.element(ui.item);
                    console.log(thisElement, thisIndex);
                    //$scope.sort(thisElement.track.unique_id, thisIndex);
                },
                helper: function (e, tr) {
                    var $originals = tr.children();
                    var $helper = tr.clone();
                    $helper.children().each(function (index) {
                        $(this).width($originals.eq(index).width())
                    });
                    return $helper;
                }
            };

        }

    ]);

    catalog.directive("trackItem", ["TIMELINE_RESOLUTION", function (TIMELINE_RESOLUTION) {
        return {
            link: function (scope, elem, attrs) {
                var width, diff, isFirst = false, isLast = false, isOutside = false, isInside = false;

                /* Define time ranges */
                var leftRange = scope.schedule.position - (TIMELINE_RESOLUTION >> 1),
                    rightRange = scope.schedule.position + (TIMELINE_RESOLUTION >> 1),
                    marginSize = (TIMELINE_RESOLUTION >> 1) - scope.schedule.position;

                if (scope.track.time_offset + scope.track.duration < leftRange || scope.track.time_offset > rightRange) {
                    isOutside = true;
                }

                if (scope.track.time_offset <= leftRange && scope.track.time_offset + scope.track.duration > leftRange) {
                    isFirst = true;
                }

                if (scope.track.time_offset >= leftRange && scope.track.time_offset + scope.track.duration <= rightRange) {
                    isInside = true;
                }

                if (scope.track.time_offset < rightRange && scope.track.time_offset + scope.track.duration >= rightRange) {
                    isLast = true;
                }

                (scope.reDraw = function () {
                    if (isFirst && isLast) {
                        width = Math.min(elem.parent().width() / scope.rate, scope.track.duration);
                        elem.css("margin-left", Math.max(0, marginSize * scope.rate).toString().concat("px"));
                    } else if (isFirst) {
                        width = Math.max(0, scope.track.duration - (leftRange - scope.track.time_offset));
                    } else if (isLast) {
                        var end = scope.track.time_offset + scope.track.duration;
                        width = Math.min(scope.track.duration - (end - rightRange), scope.track.duration);
                    } else if (isInside) {
                        width = scope.track.duration;
                        if (scope.$first) {
                            elem.css("margin-left", Math.max(0, marginSize * scope.rate).toString().concat("px"));
                        }
                    } else if (isOutside) {
                        width = 0;
                        elem.css("display", "none");
                    }
                    elem.width((width * scope.rate).toString().concat("px"));
                })();

            },
            template: '<div class="track-title">{{track.caption}}</div>'
        };
    }]);

    catalog.directive("timeLine", ["TIMELINE_RESOLUTION", function (TIMELINE_RESOLUTION) {
        return {
            scope: {
                schedule: "=timeLine"
            },
            template: '<div class="timeline-container">\
               <div class="timeline-wrap">\
                  <div ng-repeat="track in schedule.tracks" class="timeline-track" ng-class="(track.tid === current.tid) ? \'current\' : \'\'\" track-item mor-tooltip="{{track.caption}}"></div>\
               </div>\
            </div>\
            <div class="canvas-wrap"><canvas id="grid"></canvas></div>',
            controller: function ($element, $scope, $parse, $attrs) {

                $scope.rate = $element.find(".timeline-container").width() / TIMELINE_RESOLUTION;

                $scope.$watch("schedule", function (response) {

                    if (!response) return false;

                    $scope.current = response.tracks[response.current];
                    $scope.schedule = response;
                    $scope.schedule.clientTime = new Date().getTime();
                    $scope.drawGrid();

                });

                $scope.drawGrid = function () {
                    var leftEdgeTime = $scope.schedule.time - (TIMELINE_RESOLUTION >> 1);
                    var cut = leftEdgeTime % 60000;
                    var canvas = $element.find("#grid").get(0);

                    if (typeof canvas == "undefined") return;

                    canvas.height = $(canvas).height();
                    canvas.width = $(canvas).width();

                    var rangeUSeconds = TIMELINE_RESOLUTION;

                    var resolution = canvas.width / rangeUSeconds;

                    var context = canvas.getContext("2d");
                    context.font = "10px sans-serif";
                    context.translate(0.5, 0.5);
                    context.clearRect(0, 0, canvas.width, canvas.height);
                    context.globalAlpha = 1;
                    context.strokeStyle = "#000000";
                    context.beginPath();
                    var fix;
                    for (var n = -cut; n < rangeUSeconds; n += 30000) {
                        var thisDate = new Date(leftEdgeTime + n);
                        fix = parseInt(n * resolution);
                        context.moveTo(fix, 0);

                        if (thisDate.getMinutes() % 5 == 0 && thisDate.getSeconds() == 0) {
                            context.lineTo(fix, 6);
                            var time = thisDate.toTimeString().replace(/.*(\d{2}:\d{2}):\d{2}.*/, "$1");
                            context.fillText(time, fix - 12, 16);
                        } else if (thisDate.getSeconds() == 0) {
                            context.lineTo(fix, 4);
                        } else {
                            context.lineTo(fix, 2);
                        }
                    }
                    context.stroke();
                    context.globalAlpha = 0.5;
                    context.fillStyle = "#FF0000";
                    context.beginPath();
                    context.moveTo(canvas.width / 2, 0);
                    context.lineTo(canvas.width / 2 + 5, 10);
                    context.lineTo(canvas.width / 2 - 5, 10);
                    context.lineTo(canvas.width / 2, 0);
                    context.fill();
                };
            }
        }
    }]);

    catalog.factory("Categories", ["$http", "Response", function ($http, Response) {
        return {
            list: function () {
                var action = $http({
                    method: "GET",
                    url: "/api/v2/categories"
                });
                return Response(action);
            }
        }
    }]);

    catalog.factory("Resolvers", ["$http", "ResponseData", function ($http, ResponseData) {
        return {
            getChannelList: function (filter, category, offset, limit) {
                return $http.get("/api/v2/streams/getList", {
                    cache: true,
                    busy: true,
                    params: {
                        filter: filter,
                        category: category,
                        offset: offset,
                        limit: limit
                    }
                }).then(function (data) {
                    return ResponseData(data);
                });
            },
            getBookmarks: function (offset, limit) {
                return $http.get("/api/v2/streams/getBookmarks", {
                    busy: true,
                    params: {
                        offset: offset,
                        limit: limit
                    }
                }).then(function (data) {
                    return ResponseData(data);
                });
            },
            getByIdWithSimilar: function (streamID) {
                return $http.get("/api/v2/streams/getOneWithSimilar", {
                    busy: true,
                    params: {
                        stream_id: streamID
                    }
                }).then(function (data) {
                    return ResponseData(data);
                });
            },
            getChannelsByUser: function (userID, offset, limit) {
                return $http.get("/api/v2/streams/getStreamsByUser", {
                    busy: true,
                    params: {
                        user: userID,
                        offset: offset,
                        limit: limit
                    }
                }).then(function (data) {
                    return ResponseData(data);
                });
            },
            getChannelsBySelf: function (offset, limit) {
                return $http.get("/api/v2/streams/getStreamsByUser", {
                    busy: true,
                    params: {
                        offset: offset,
                        limit: limit
                    }
                }).then(function (data) {
                    return ResponseData(data);
                });
            }
        }
    }]);

    catalog.factory("Streams", ["$http", "Response", "ResponseData",

        function ($http, Response, ResponseData) {
            return {
                getByID: function (streamID) {
                    var action = $http({
                        method: "GET",
                        url: "/api/v2/streams/getOne",
                        busy: false,
                        params: {
                            stream_id: streamID
                        }
                    });
                    return Response(action);
                },
                getList: function (filter, category, offset, limit, busy) {
                    var action = $http({
                        method: "GET",
                        cache: true,
                        url: "/api/v2/streams/getList",
                        busy: busy,
                        params: {
                            filter: filter,
                            category: category,
                            offset: offset,
                            limit: limit
                        }
                    });
                    return Response(action);
                },
                getSimilarTo: function (streamID) {
                    var action = $http({
                        method: "GET",
                        cache: true,
                        url: "/api/v2/streams/getSimilarTo",
                        params: {
                            stream_id: streamID
                        }
                    });
                    return Response(action);
                },
                getByIdWithSimilar: function (streamID) {
                    var promise = $http.get("/api/v2/streams/getOneWithSimilar", {
                        cache: true,
                        url: "/api/v2/streams/getOneWithSimilar",
                        busy: true,
                        params: {
                            stream_id: streamID
                        }
                    }).success(function (data) {
                        return ResponseData(data);
                    });

                    return promise;
                },
                getByUser: function (userID, offset, busy) {
                    var action = $http({
                        method: "GET",
                        url: "/api/v2/streams/getStreamsByUser",
                        busy: busy,
                        params: {
                            user: userID,
                            offset: offset
                        }
                    });
                    return Response(action);
                },
                getBySelf: function () {
                    var action = $http({
                        method: "GET",
                        url: "/api/v2/streams/getStreamsByUser"
                    });
                    return Response(action);
                },
                getBookmarks: function (offset, busy) {
                    var action = $http({
                        method: "GET",
                        cache: true,
                        url: "/api/v2/streams/getBookmarks",
                        busy: busy,
                        params: {
                            offset: offset
                        }
                    });
                    return Response(action);
                },
                getSchedule: function (streamID) {
                    var action = $http({
                        method: "GET",
                        ignoreLoadingBar: true,
                        url: "/api/v2/streams/getSchedule",
                        params: {
                            stream_id: streamID
                        }
                    });
                    return Response(action);
                },
                getNowPlaying: function (streamID) {
                    var action = $http({
                        method: "GET",
                        ignoreLoadingBar: true,
                        url: "/api/v2/streams/getNowPlaying",
                        params: {
                            stream_id: streamID
                        }
                    });
                    return Response(action);
                },
                updateStreamInfo: function (object) {
                    var action = $http({
                        method: "POST",
                        url: "/api/v2/stream/modify",
                        data: {
                            stream_id: object.sid,
                            name: object.name,
                            info: object.info,
                            tags: object.hashtags,
                            permalink: object.permalink,
                            category: object.category,
                            access: object.access
                        }
                    });
                    return Response(action);
                },
                createNewStream: function (object) {
                    var action = $http({
                        method: "POST",
                        url: "/api/v2/stream/create",
                        data: {
                            name: object.name,
                            info: object.info,
                            tags: object.hashtags,
                            permalink: object.permalink,
                            category: object.category,
                            access: object.access
                        }
                    });
                    return Response(action);
                },
                deleteStream: function (stream) {
                    var action = $http({
                        method: "POST",
                        url: "/api/v2/stream/delete",
                        data: {
                            stream_id: stream.sid
                        }
                    });
                    return Response(action);
                }
            }
        }
    ]);

})();