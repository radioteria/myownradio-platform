/**
 * Loader: Stage 1
 */

(function () {
    "use strict";
    window.mor = {};

    mor.resources = {};

    mor.scripts = [
        "/js/jquery-1.11.0.min.js.gz",
        "/js/jquery.tmpl.js",
        "/js/activewrappers.min.js",
        "/js/controller.js"
    ];

    mor.styles = [
        "/css/reset.css",
        "/css/mor-common.css"
    ];

    mor.addScript = function (url) {
        var js;
        js = document.createElement("script");
        js.type = "text/javascript";
        js.src = url;
        document.getElementsByTagName('head')[0].appendChild(js);
    };
    mor.addStyle = function (url) {
        var css;
        css = document.createElement("link");
        css.rel = "stylesheet";
        css.type = "text/css";
        css.href = url;
        document.getElementsByTagName('head')[0].appendChild(css);
    };

    mor.loadScripts = function () {
        for (var i = 0; i < mor.scripts.length; i += 1) {
            mor.addScript(mor.scripts[i]);
        }
    };
    mor.loadStyles = function () {
        for (var i = 0; i < mor.styles.length; i += 1) {
            mor.addStyle(mor.styles[i]);
        }
    };
    mor.loadTemplates = function () {
        $.get("/tools/templates", function (json) {
            mor.resources.template = json;
            mor.stage2();
        });
    };

    mor.initApplication = function () {
        // Load all JS scripts
        mor.loadScripts();
        // Load all CSS files
        mor.loadStyles();
    };

    // Start!
    console.log("Starting engine");
    mor.initApplication();
})();
