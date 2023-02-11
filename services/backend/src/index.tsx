import './bootstrap' // <-- This import should be first in list!
import 'reflect-metadata'
import ng, { IScope } from 'angular'
import { configure as configureMobX } from 'mobx'
import makeDebug from 'debug'
import { AppStore } from './store'
import { RadioPlayerStatus } from './entries/RadioPlayer'

const debug = makeDebug('main')

configureMobX({
  computedRequiresReaction: true,
  reactionRequiresObservable: true,
  enforceActions: 'always',
})

const appStore = new AppStore()

ng.module('application')
  .constant('store', appStore)
  .run([
    '$rootScope',
    'store',
    ($rootScope: IScope, { radioPlayerStore }: AppStore) => {
      $rootScope.$watch('defaults.format', (format: string) => {
        if (radioPlayerStore.state.status === RadioPlayerStatus.Playing) {
          debug('Restarting playback due to default format change: %s', format)
          radioPlayerStore.play(radioPlayerStore.state.id, format)
        }
      })
    },
  ])

Object.assign(window, { appStore })
