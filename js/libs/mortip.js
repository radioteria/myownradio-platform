/*
 MOR Tooltip plugin v0.1
 */

(function () {

    var FAST_TOOLTIP_DELAY = 50;

    $(document).ready(function () {
        var tipTemplate = $("<div>")
                .addClass("mortip")
                .append($("<div>").addClass("corner"))
                .append($("<div>").addClass("content"))
                .prependTo("body"),
            tipDelay = null,
            hideOn = new Date().getTime(),
            showTip = function ($target, contents, delay, raw) {

                getRealWidth($target);

                var targetW = $target.outerWidth(),
                    targetH = $target.outerHeight(),
                    targetX1 = $target.offset().left,
                    targetY1 = $target.offset().top,
                    targetY2 = $target.offset().top + targetH,
                    scrollTop = $(window).scrollTop(),
                    windowHeight = $(window).height(),
                    documentWidth = $(document).width();

                if (raw === true) {
                    tipTemplate.find(".content").html(contents);
                } else {
                    tipTemplate.find(".content").text(contents);
                }


                var width = tipTemplate.outerWidth(true),
                    newLeft = targetX1 + (targetW / 2) - (width / 2),
                    leftShift = Math.max(0, newLeft + width - documentWidth),
                    rightShift = Math.max(0, -newLeft);

                tipTemplate.css("left", newLeft - leftShift + rightShift);

                if (targetY1 + (targetH / 2) - scrollTop > (windowHeight / 2)) {
                    tipTemplate.css("top", (targetY1 - tipTemplate.outerHeight(true) - 8).toString().concat("px"));
                    tipTemplate.addClass("top");
                } else {
                    tipTemplate.css("top", targetY2.toString().concat("px"));
                    tipTemplate.addClass("bottom");
                }

                tipTemplate.find(".corner").css({left: (width / 2 + leftShift - rightShift).toString().concat("px")});

                showSlow(delay || 500);

            },
            getRealWidth = function (element) {


            },
            showFast = function () {
                tipTemplate.addClass("visible");
                tipTemplate.css({opacity: 1});
            },
            showSlow = function (delay) {
                resetDelay();
                tipDelay = window.setTimeout(function () {
                    tipTemplate.addClass("visible");
                    tipTemplate.animate({opacity: 1}, 250);
                }, delay);
            },
            resetDelay = function () {
                if (tipDelay !== null) {
                    window.clearInterval(tipDelay);
                    tipDelay = null;
                }
            },
            hideTip = function () {
                hideOn = new Date().getTime();
                resetDelay();
                tipTemplate
                    .stop()
                    .removeClass("visible top bottom")
                    .css({
                        top: "",
                        left: "",
                        bottom: "",
                        right: "",
                        opacity: 0
                    });
            },
            initPlugin = function () {

                var ajaxHandle = null,
                    timerHandle = null,
                    stop = function () {
                        if(ajaxHandle && ajaxHandle.readystate != 4){
                            ajaxHandle.abort();
                        }
                        if(timerHandle) {
                            window.clearInterval(timerHandle);
                        }
                    };

                $("[mor-tooltip]").livequery(function () {

                    var $this = $(this);

                    $this.on("mouseover", function () {
                        showTip($this, $this.attr("mor-tooltip"), undefined, false);
                    }).on("mouseleave click", function () {
                        hideTip();
                    });


                });

                $("[mor-tooltip-url]").livequery(function () {

                    var $this = $(this);

                    $this.on("mouseover", function () {

                        stop();
                        timerHandle = window.setTimeout(function () {
                            ajaxHandle = $.get($this.attr("mor-tooltip-url"));
                            ajaxHandle.then(function (data) {
                                showTip($this, data, 0, true);
                            });
                        }, 250);

                    }).on("mouseleave click", function () {
                        stop();
                        hideTip()
                    });

                });

            };

        initPlugin();

    });

})();