<?php


use Framework\Handlers;
use Framework\Handlers\api;
use Framework\Handlers\content;
use Framework\Handlers\helpers;
use Framework\Services\SubRouter;
use Framework\View\Errors\View404Exception;


$sub = SubRouter::getInstance();


/* Public side routes register */
$sub->addRoute("content/application.modules.js", content\DoGetJavascriptModules::class);
$sub->addRoute("content/application.config.js", content\DoGetJavascriptSettings::class);

/* Dashboard redirect */
$sub->addRouteRegExp("~^profile(\\/.+)*$~", content\DoDashboard::class);

$sub->addRoutes([
    "index",
    "streams",
    "bookmarks",
    "login",
    "recover",
    "recover/:code",
    "tag/:tag",
    "signup",
    "signup/:code",
    "static/registrationLetterSent",
    "static/registrationCompleted",
    "static/resetLetterSent",
    "static/resetPasswordCompleted",
    "categories"
], content\DoDefaultTemplate::class);

$sub->addRoute("category/:category", helpers\DoCategory::class);
$sub->addRoute("streams/:id", helpers\DoStream::class);
$sub->addRoute("user/:id", helpers\DoUser::class);
$sub->addRoute("search/:query", helpers\DoSearch::class);

$sub->addRoute("content/streamcovers/:fn", content\DoGetStreamCover::class);
$sub->addRoute("content/avatars/:fn", content\DoGetUserAvatar::class);
$sub->addRoute("content/audio/&id", content\DoGetPreviewAudio::class);
$sub->addRoute("content/m3u/:stream_id.m3u", content\DoM3u::class);
$sub->addRoute("content/trackinfo/&id", content\DoTrackExtraInfo::class);

$sub->addRoute("subscribe", api\v3\DoAcquire::class);


$sub->addRoute("test/&id", Handlers\DoTest::class);

// Default route
$sub->defaultRoute(function () {
    throw new View404Exception("try again later or try another page");
});


/* Nothing */

