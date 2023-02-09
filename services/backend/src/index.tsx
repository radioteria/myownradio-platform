import './bootstrap' // <-- This import should be first in list!
import 'reflect-metadata'
import ng from 'angular'
import React from 'react'
import { configure as configureMobX } from 'mobx'
import { makeReactApp } from './reactInterop'
import { RadioPlayerComponent } from './entries/RadioPlayer'
import { AppStore } from './store'
import { Observer } from 'mobx-react-lite'

configureMobX({
  computedRequiresReaction: true,
  reactionRequiresObservable: true,
  enforceActions: 'always',
})

const appStore = new AppStore()
const { audioPlayerStore } = appStore

ng.module('application')
  .constant('store', appStore)
  .directive(
    'reactRadioPlayer',
    makeReactApp(
      <Observer>
        {() => (
          <RadioPlayerComponent
            src={audioPlayerStore.src}
            onBufferingStatusChange={audioPlayerStore.setBufferingStatus}
            onBufferedAmountChange={audioPlayerStore.setBufferedAmount}
            onCurrentTimeChange={audioPlayerStore.setCurrentTime}
          />
        )}
      </Observer>,
    ),
  )

Object.assign(window, { appStore })
