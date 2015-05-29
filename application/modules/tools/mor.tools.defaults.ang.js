(function () {
    var tools = angular.module("mor.tools");

    tools.run(["$rootScope", "Defaults", function ($rootScope, Defaults) {

        $rootScope.defaults = {
            formats: Defaults.getFormatsList(),
            format: Defaults.getDefaultFormat()
        };

        $rootScope.setDefaultFormat = function (format) {
            $rootScope.defaults.format = format;
            Defaults.setDefaultFormat(format);
            $rootScope.player.controls.reload();
        };

        $rootScope.config = $CONFIG;

    }]);

    tools.factory("Defaults", [function () {
            return {
                getDefaultFormat: function () {
                    return $.cookie("af") || "mp3_128k";
                },
                setDefaultFormat: function (format) {
                    $.cookie("af", format, { expires: 365, path: "/" });
                },
                getFormatsList: function () {
                    return {
                        aac: [
                            { key: "aacplus_24k", bitrate: "24K" },
                            { key: "aacplus_32k", bitrate: "32K" },
                            { key: "aacplus_64k", bitrate: "64K" },
                            { key: "aacplus_128k", bitrate: "128K"}
                        ],
                        mp3: [
                            { key: "mp3_64k", bitrate: "64K" },
                            { key: "mp3_128k", bitrate: "128K" },
                            { key: "mp3_256k", bitrate: "256K" }
                        ]
                    }
                }
            }
        }
    ]);
})();