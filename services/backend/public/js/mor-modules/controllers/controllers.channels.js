/**
 * Created by roman on 05.04.15.
 */
(function () {

    var module = angular.module("application");

    module.controller("ChannelView", ["channelData", "similarData", "$scope", "$channels", "ChannelListActions", "TrackListActions", "store",
        function (channelData, similarData, $scope, $channels, ChannelListActions, TrackListActions, store) {
            $scope.radioPlayerStore = store.radioPlayerStore

            $scope.data = channelData;
            $scope.data.channels = similarData.channels;
            $scope.action = ChannelListActions(channelData.channel);
            $scope.trackAction = TrackListActions;
            $scope.actionProvider = ChannelListActions;

            //$scope.$on("event", function (e, data) {
            //    var event = data.data;
            //    if (event.subject == $scope.data.channel.sid) {
            //        if (event.event === "listener.new") {
            //            $scope.data.channel.listeners_count += 1;
            //        } else if (event.event === "listener.gone") {
            //            $scope.data.channel.listeners_count = Math.max($scope.data.channel.listeners_count - 1, 0);
            //        } else if (event.event === "bookmark.new") {
            //            $scope.data.channel.bookmarks_count += 1;
            //        } else if (event.event === "bookmark.remove") {
            //            $scope.data.channel.bookmarks_count = Math.max($scope.data.channel.bookmarks_count - 1, 0);
            //        }
            //    }
            //});
        }
    ]);

    module.controller("ChannelListCategory", ["channelsData", "$scope", "$channels", "ChannelListActions",

        function (channelsData, $scope, $channels, ChannelListActions) {
            $scope.data = channelsData;
            $scope.empty = channelsData.channels.items.length == 0;
            $scope.busy = false;
            $scope.end = false;
            $scope.actionProvider = ChannelListActions;
            $scope.load = function () {
                $scope.busy = true;
                $channels.getCategoryChannels($scope.data.channels.items.length).then(function (data) {
                    for (var i = 0; i < data.channels.items.length; i++) {
                        $scope.data.channels.items.push(data.channels.items[i]);
                    }
                    if (data.channels.items.length > 0) {
                        $scope.busy = false;
                    } else {
                        $scope.end = true;
                    }
                });
            }
        }

    ]);

    module.controller("ChannelListTag", ["channelsData", "$scope", "$channels", "ChannelListActions", "$routeParams",

        function (channelsData, $scope, $channels, ChannelListActions, $routeParams) {
            $scope.data = channelsData;
            $scope.empty = channelsData.channels.items.length == 0;
            $scope.busy = false;
            $scope.end = false;
            $scope.actionProvider = ChannelListActions;
            $scope.load = function () {
                $scope.busy = true;
                $channels.getTagChannels($routeParams.tag, $scope.data.channels.items.length).then(function (data) {
                    for (var i = 0; i < data.channels.items.length; i++) {
                        $scope.data.channels.items.push(data.channels.items[i]);
                    }
                    if (data.channels.items.length > 0) {
                        $scope.busy = false;
                    } else {
                        $scope.end = true;
                    }
                });
            }
        }

    ]);

    module.controller("ChannelListSearch", ["channelsData", "$scope", "$channels", "ChannelListActions", "$routeParams",

        function (channelsData, $scope, $channels, ChannelListActions, $routeParams) {
            $scope.data = channelsData;
            $scope.empty = channelsData.channels.items.length == 0;
            $scope.busy = false;
            $scope.end = false;
            $scope.actionProvider = ChannelListActions;
            $scope.load = function () {
                $scope.busy = true;
                $channels.getSearchChannels($routeParams.query, $scope.data.channels.items.length).then(function (data) {
                    for (var i = 0; i < data.channels.items.length; i++) {
                        $scope.data.channels.items.push(data.channels.items[i]);
                    }
                    if (data.channels.items.length > 0) {
                        $scope.busy = false;
                    } else {
                        $scope.end = true;
                    }
                });
            }
        }

    ]);

    module.controller("ChannelListUser", ["channelsData", "$scope", "$channels", "ChannelListActions", "$routeParams",

        function (channelsData, $scope, $channels, ChannelListActions, $routeParams) {
            $scope.data = channelsData;
            $scope.data.name = channelsData.user.name ? channelsData.user.name : channelsData.user.login;
            $scope.empty = channelsData.channels.items.length == 0;
            $scope.busy = false;
            $scope.end = false;
            $scope.actionProvider = ChannelListActions;
            $scope.load = function () {
                $scope.busy = true;
                $channels.getUserChannels($routeParams.key, $scope.data.channels.items.length).then(function (data) {
                    for (var i = 0; i < data.channels.items.length; i++) {
                        $scope.data.channels.items.push(data.channels.items[i]);
                    }
                    if (data.channels.items.length > 0) {
                        $scope.busy = false;
                    } else {
                        $scope.end = true;
                    }
                });
            }
        }

    ]);

    module.controller("ChannelListMe", ["channelsData", "$scope", "$channels", "ChannelListActions",

        function (channelsData, $scope, $channels, ChannelListActions) {
            $scope.data = channelsData;
            $scope.data.name = channelsData.user.name ? channelsData.user.name : channelsData.user.login;
            $scope.empty = channelsData.channels.items.length == 0;
            $scope.busy = false;
            $scope.end = false;
            $scope.actionProvider = ChannelListActions;
            $scope.load = function () {
                $scope.busy = true;
                $channels.getMyChannels($scope.data.channels.items.length).then(function (data) {
                    for (var i = 0; i < data.channels.items.length; i++) {
                        $scope.data.channels.items.push(data.channels.items[i]);
                    }
                    if (data.channels.items.length > 0) {
                        $scope.busy = false;
                    } else {
                        $scope.end = true;
                    }
                });
            }
        }

    ]);

    module.controller("ChannelListPopular", ["channelsData", "$scope", "$channels", "ChannelListActions",

        function (channelsData, $scope, $channels, ChannelListActions) {
            $scope.data = channelsData;
            $scope.empty = channelsData.channels.items.length == 0;
            $scope.busy = false;
            $scope.end = false;
            $scope.actionProvider = ChannelListActions;
            $scope.load = function () {
                $scope.busy = true;
                $channels.getPopularChannels($scope.data.channels.items.length).then(function (data) {
                    for (var i = 0; i < data.channels.items.length; i++) {
                        $scope.data.channels.items.push(data.channels.items[i]);
                    }
                    if (data.channels.items.length > 0) {
                        $scope.busy = false;
                    } else {
                        $scope.end = true;
                    }
                });
            }
        }

    ]);

    module.controller("ChannelListNew", ["channelsData", "$scope", "$channels", "ChannelListActions",

        function (channelsData, $scope, $channels, ChannelListActions) {
            $scope.data = channelsData;
            $scope.empty = channelsData.channels.items.length == 0;
            $scope.busy = false;
            $scope.end = false;
            $scope.actionProvider = ChannelListActions;
            $scope.load = function () {
                $scope.busy = true;
                $channels.getNewChannels($scope.data.channels.items.length).then(function (data) {
                    for (var i = 0; i < data.channels.items.length; i++) {
                        $scope.data.channels.items.push(data.channels.items[i]);
                    }
                    if (data.channels.items.length > 0) {
                        $scope.busy = false;
                    } else {
                        $scope.end = true;
                    }
                });
            }
        }

    ]);


    module.controller("ChannelListRecent", ["channelsData", "$scope", "$channels", "ChannelListActions",

        function (channelsData, $scope, $channels, ChannelListActions) {
            $scope.data = channelsData;
            $scope.empty = channelsData.channels.items.length === 0;
            $scope.busy = false;
            $scope.end = false;
            $scope.actionProvider = ChannelListActions;
            $scope.load = function () {
                $scope.busy = true;
                $channels.getRecentChannels($scope.data.channels.items.length).then(function (data) {
                    for (var i = 0; i < data.channels.items.length; i++) {
                        $scope.data.channels.items.push(data.channels.items[i]);
                    }
                    if (data.channels.items.length > 0) {
                        $scope.busy = false;
                    } else {
                        $scope.end = true;
                    }
                });
            }
        }

    ]);

    module.controller("ChannelListBookmarks", ["channelsData", "$scope", "$channels", "ChannelListActions",

        function (channelsData, $scope, $channels, ChannelListActions) {
            $scope.data = channelsData;
            $scope.empty = channelsData.channels.items.length == 0;
            $scope.busy = false;
            $scope.end = false;
            $scope.actionProvider = ChannelListActions;
            $scope.load = function () {
                $scope.busy = true;
                $channels.getBookmarkedChannels($scope.data.channels.items.length).then(function (data) {
                    for (var i = 0; i < data.channels.items.length; i++) {
                        $scope.data.channels.items.push(data.channels.items[i]);
                    }
                    if (data.channels.items.length > 0) {
                        $scope.busy = false;
                    } else {
                        $scope.end = true;
                    }
                });
            }
        }

    ]);

    module.factory("ChannelListActions", ["$channels", "$bookmarks", "Popup", "$rootScope", "TrackAction",

        function ($channels, $bookmarks, Popup, $rootScope, TrackAction) {
            return function (channel) {
                return {
                    bookmark: function () {
                        if (channel.bookmarked === 1) {
                            $bookmarks.removeBookmark(channel).then(function () {
                                Popup.message($rootScope.tr("FR_BOOKMARK_REMOVE_SUCCESS", [ channel.name ]));
                                channel.bookmarked = 0;
                                channel.bookmarks_count --;
                            }, function (message) {
                                Popup.message(message);
                            });
                        } else {
                            $bookmarks.addBookmark(channel).then(function () {
                                Popup.message($rootScope.tr("FR_BOOKMARK_ADD_SUCCESS", [ channel.name ]));
                                channel.bookmarked = 1;
                                channel.bookmarks_count ++;
                            }, function (message) {
                                Popup.message(message);
                            });
                        }
                    },
                    play: function () {
                        $rootScope.player.controls.playSwitchStream(channel);
                    },
                    removeTrack: function (track) {
                        TrackAction.removeTracksFromStream(channel, [track]);
                    }
                }
            }
        }

    ]);

    module.filter("channelArtwork", [function () {
        return function (source) {
            return "content/streamcovers/" + source;
        }
    }]);


    module.filter("channelLink", [function () {
        return function (channel) {
            if (angular.isObject(channel)) {
                return "/streams/" + (channel.permalink ? channel.permalink : channel.sid);
            } else {
                return undefined;
            }
        }
    }]);

})();
