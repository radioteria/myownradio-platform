import 'jquery'
import angular from 'angular'
import 'angular-route'
import 'angular-animate'
import 'angular-mixpanel'
import 'angular-contenteditable/angular-contenteditable.js'

import 'jquery-migrate/jquery-migrate.min.js'
import 'jquery-ui/jquery-ui.min.js'
import 'jquery.cookie/jquery.cookie.js'
import 'jquery-livequery/dist/jquery.livequery.min.js'
import 'jPlayer/dist/jplayer/jquery.jplayer.min.js'
import 'mixpanel/mixpanel-jslib-snippet.min.js'

import 'modules/ng-infinite-scroll.min.js'
import 'modules/angular-post-fix.js'
import 'modules/loading-bar.min.js'
import 'modules/ngDialog.min.js'
import 'modules/ng-context-menu.js'
import 'modules/angulartics.min.js'
import 'modules/angulartics-ga.min.js'
import 'modules/angular-seo.js'
import 'libs/sortable.js'
import 'libs/angular-touch.js'

import 'mor-modules/main.ang.js'
import 'mor-modules/filters.js'
import 'mor-modules/context.js'

import 'mor-modules/site.js'
import 'mor-modules/tools/mor.tools.ang.js'
import 'mor-modules/tools/mor.tools.defaults.ang.js'
import 'mor-modules/tools/mor.tools.share.ang.js'
import 'mor-modules/tools/mor.tools.stats.ang.js'
import 'mor-modules/tools/mor.tools.image.ang.js'

import 'mor-modules/account.js'
import 'mor-modules/player.js'
import 'mor-modules/catalog.js'
import 'mor-modules/search.js'
import 'mor-modules/profile.js'
import 'mor-modules/library.js'
import 'mor-modules/audioinfo.js'
import 'mor-modules/loader.js'
import 'mor-modules/track-action.js'
import 'mor-modules/popup.ang.js'
import 'mor-modules/mor.stream.scheduler.js'

import 'mor-modules/api/api.core.js'
import 'mor-modules/api/api.channels.js'
import 'mor-modules/api/api.streams.js'
import 'mor-modules/api/api.categories.js'
import 'mor-modules/api/api.tracks.js'
import 'mor-modules/api/api.bookmarks.js'
import 'mor-modules/api/api.schedule.js'

import 'mor-modules/filters/filter.object.js'

import 'mor-modules/controllers/controllers.channels.js'
import 'mor-modules/controllers/controllers.tracks.js'

import 'mor-modules/directives/directives.player.js'

import 'mor-modules/ui/ui.hashtags.js'
import 'libs/mortip.js'
import 'functions.js'

angular.module('application').directive('fooBar', [
  () => {
    return {
      restrict: 'A',
      multiElement: true,
      link: (_scope, _element, _attrs) => {
        // NOP
      },
    }
  },
])
