import './bootstrap' // <-- This import should be first in list!
import 'reflect-metadata'
import React from 'react'
import ng, { IScope } from 'angular'
import { configure as configureMobX } from 'mobx'
import makeDebug from 'debug'
import { AppStore } from './store'
import { RadioPlayerStatus } from './entries/RadioPlayer'
import { RadioPlayerStats } from './entries/RadioPlayer/RadioPlayerStats'
import { StreamPreview } from './entries/StreamPreview/StreamPreview'
import { makeReactApp, makeReactAppWithAttrs } from './reactInterop'

const debug = makeDebug('main')

configureMobX({
  computedRequiresReaction: true,
  reactionRequiresObservable: true,
  enforceActions: 'always',
})

const appStore = new AppStore()

ng.module('application')
  .constant('$store', appStore)
  .directive(
    'radioPlayerStats',
    makeReactApp(<RadioPlayerStats radioPlayerStore={appStore.radioPlayerStore} />),
  )
  .directive(
    'streamPreview',
    makeReactAppWithAttrs(
      ({ streamId }) => {
        return <StreamPreview streamId={streamId} />
      },
      ['streamId'],
    ),
  )
  .run([
    '$rootScope',
    '$store',
    ($rootScope: IScope, $store: AppStore) => {
      $rootScope.$watch('defaults.format', (format: string) => {
        if ($store.radioPlayerState.status === RadioPlayerStatus.Playing) {
          debug('Restarting playback due to default format change: %s', format)
          $store.playChannel($store.radioPlayerState.channel, format)
        }
      })

      Object.assign($rootScope, { $store })
    },
  ])

Object.assign(window, { appStore })
