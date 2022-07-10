/**
 * Created by roman on 05.04.15.
 */
(function() {

    var api = angular.module("application");

    api.factory("$streams", ["$http", "Response", function($http, Response) {
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
      };
    }]);

})();
