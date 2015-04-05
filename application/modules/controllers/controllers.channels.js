/**
 * Created by roman on 05.04.15.
 */

(function () {

    var module = angular.module("application");

    module.controller("ChannelListCategory", ["channelsData", "$scope", "$channels", function (channelsData, $scope, $channels) {
        $scope.data = channelsData;
        $scope.empty = channelsData.channels.items.length == 0;
        $scope.busy = false;
        $scope.load = function () {
            $scope.busy = true;
            $channels.getCategoryChannels($scope.data.channels.length).then(function (data) {
                for (var i = 0; i < data.channels.length; i ++) {
                    $scope.data.channels.items[null] = data.channels.items[i];
                }
                $scope.busy = false;
            });
        }
    }]);

    module.factory("ChannelListActions", ["$channels", function ($channels) {

    }]);

    module.filter("channelArtwork", [function () {
        return function (source) {
            return "content/streamcovers/" + source
        }
    }]);

})();