import './bootstrap' // <-- This import should be first in list!
import 'reflect-metadata'
import ng from 'angular'
import { configure as configureMobX } from 'mobx'
import { AppStore } from './store'

configureMobX({
  computedRequiresReaction: true,
  reactionRequiresObservable: true,
  enforceActions: 'always',
})

const appStore = new AppStore()

ng.module('application').constant('store', appStore)

Object.assign(window, { appStore })
