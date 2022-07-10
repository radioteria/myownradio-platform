/**
 * Module Catalog
 */
(function () {

    var catalog = angular.module("Catalog", ["Site"]);

    catalog.constant("STREAMS_PER_SCROLL", 20);
    catalog.constant("TIMELINE_RESOLUTION", 1800000);


    catalog.controller("StreamEditorController", ["$scope", "$rootScope", "$routeParams", "$streams", "$location", "$dialog",

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

    catalog.controller("NewStreamController", ["$scope", "$streams", "$rootScope", "Response", "$http",

        function ($scope, Streams, $rootScope, Response, $http) {
            $scope.status = "";
            $scope.error = "";
            $scope.stream = {
                name: "",
                info: "",
                hashtags: "",
                permalink: "",
                category: 16,
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

    catalog.controller("SearchFormController", ["$element", "$scope", "$location", "$streams", "$document", "$channels",

        function ($element, $scope, $location, Streams, $document, $channels) {
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
            $scope.goStream = function ($channel) {
                var key = $channel.permalink || $channel.sid;
                $location.url("/streams/" + key);
                $scope.filter = "";
            };
            $scope.$watch("filter", function (value) {
                if (typeof value == "string" && value.length > 0) {
                    $channels.getSuggestChannels($scope.filter).then(function (data) {
                        $scope.streams = data;
                    });
                } else {
                    $scope.streams = [];
                }
            });
        }

    ]);


    catalog.controller("ListStreamsController", ["$scope", "$location", "$streams", "STREAMS_PER_SCROLL", "channelData",

        function ($scope, $location, Streams, STREAMS_PER_SCROLL, channelData) {

            var category = $location.search().category;

            $scope.content = {
                streams: [],
                empty: false
            };

            channelData.onSuccess(function (data) {
                $scope.content.streams = data.streams;
                $scope.content.empty = data.streams.length == 0;
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

    catalog.controller("ListSearchController", ["$scope", "$location", "$routeParams", "$streams", "STREAMS_PER_SCROLL", "channelData",

        function ($scope, $location, $routeParams, Streams, STREAMS_PER_SCROLL, channelData) {

            var category = $location.search().category;

            $scope.query = decodeURI($routeParams.query);

            $scope.content = {
                streams: [],
                empty: false
            };

            channelData.onSuccess(function (data) {
                $scope.content.streams = data.streams;
                $scope.content.empty = data.streams.length == 0;
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

    catalog.controller("BookmarksController", ["$scope", "$location", "$streams", "STREAMS_PER_SCROLL", "channelData",

        function ($scope, $location, Streams, STREAMS_PER_SCROLL, channelData) {
            $scope.content = {
                streams: [],
                empty: false
            };

            channelData.onSuccess(function (data) {
                $scope.content.streams = data;
                $scope.content.empty = data.length == 0;
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

    catalog.controller("UserStreamsController", ["$scope", "$routeParams", "$streams", "STREAMS_PER_SCROLL", "$document",

        function ($scope, $routeParams, Streams, STREAMS_PER_SCROLL, $document) {
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

                        $document.get(0).title = htmlEscape(response.user.name) + "'s radio channels on " + SITE_TITLE;

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

    catalog.controller("MyStreamsController", ["$rootScope", "$scope", "$dialog", "$streams", "StreamWorks", "TrackAction", "Popup",

        function ($rootScope, $scope, $dialog, Streams, StreamWorks, TrackAction, Popup) {

            $scope.deleteStream = function ($stream) {
                TrackAction.deleteStream($stream, function () {
                    Popup.tr("FR_STREAM_DELETED_SUCCESSFULLY", $stream);
                    $rootScope.account.init("/profile/streams/");
                });
            };

            $scope.changeStreamState = function (stream) {
                if (stream.status == 0) {
                    StreamWorks.startStream(stream).onSuccess(function () {
                        $rootScope.account.init();
                    });
                } else {
                    $dialog.question($rootScope.tr("FR_CONFIRM_STREAM_STOP", [ stream.name ]), function () {
                        StreamWorks.stopStream(stream).onSuccess(function () {
                            $rootScope.account.init();
                        });
                    });
                }
            }

        }

    ]);

    catalog.controller("OneStreamController", ["$rootScope", "$scope", "$location", "$streams", "$routeParams",
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
            $scope.copyTrack = function ($track) {
                TrackAction.copyTrackToSelf($track);
            };


        }

    ]);

    catalog.directive("trackItem", ["TIMELINE_RESOLUTION", function (TIMELINE_RESOLUTION) {
        return {
            link: function (scope, elem, attrs) {

                scope.resizeItem = function () {
                    var width, diff, isFirst = false, isLast = false, isOutside = false, isInside = false, isFull = false;

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

                    if (scope.track.time_offset < leftRange && scope.track.time_offset + scope.track.duration > rightRange) {
                        isFull = true;
                    }

                    //console.log(isOutside, isFirst, isInside, isLast, isFull);

//                    (scope.reDraw = function () {
                        if (isFull) {
                            width = elem.parent().width() / scope.rate;
                        } else if (isFirst && isLast) {
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
//                    })();
                };

                scope.resizeItem();
                scope.$on("resize", scope.resizeItem);

            },
            template: '<div class="track-title">{{ track.caption }}</div>'
        };
    }]);

    catalog.directive("timeLine", [function () {
        return {
            scope: {
                schedule: "=timeLine"
            },
            template: '<div class="timeline-container">\
               <div class="timeline-wrap">\
                  <div ng-repeat="track in schedule.tracks" class="timeline-track" ng-class="(schedule.current == $index) ? \'current\' : \'\'\" track-item mor-tooltip="{{track.caption}}"></div>\
               </div>\
            </div>\
            <div class="canvas-wrap"><canvas id="grid"></canvas></div>',

            controller: ["$element", "$scope", "$parse", "$attrs", "TIMELINE_RESOLUTION", "$window",

                function ($element, $scope, $parse, $attrs, TIMELINE_RESOLUTION, $window) {

                    $scope.$watch("schedule", function () {

                        if (!angular.isObject($scope.schedule)) return;

                        $scope.current = $scope.schedule.tracks[$scope.schedule.current];
                        $scope.schedule.clientTime = new Date().getTime();
                        $scope.drawGrid();

                    });

                    $scope.init = function () {
                        $scope.rate = $element.find(".timeline-container").width() / TIMELINE_RESOLUTION;
                        if (angular.isObject($scope.schedule)) {
                            $scope.$broadcast("resize");
                            $scope.drawGrid();
                        }
                    };

                    $scope.drawGrid = function () {
                        var leftEdgeTime = $scope.schedule.time - (TIMELINE_RESOLUTION >> 1);
                        var cut = leftEdgeTime % 60000;
                        var canvas = $element.find("#grid").get(0);

                        if (typeof canvas == "undefined") return;

                        canvas.height = $(canvas).height();
                        canvas.width = $(canvas).width();

                        var resolution = canvas.width / TIMELINE_RESOLUTION;

                        var context = canvas.getContext("2d");
                        context.font = "10px sans-serif";
                        context.translate(0.5, 0.5);
                        context.clearRect(0, 0, canvas.width, canvas.height);
                        context.globalAlpha = 1;
                        context.strokeStyle = "#000000";
                        context.beginPath();
                        var fix;
                        for (var n = -cut; n < TIMELINE_RESOLUTION; n += 30000) {
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

                    $scope.init();

                    angular.element($window).bind("resize", $scope.init);

                    $scope.$on("$destroy", function () {
                        angular.element($window).unbind("resize", $scope.init);
                    });

                }
            ]
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

})();
