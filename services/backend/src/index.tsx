import 'reflect-metadata'
import './bootstrap'
import { AppStore } from './store'
import ng from 'angular'
import { configure as configureMobX } from 'mobx'

configureMobX({
  computedRequiresReaction: true,
  reactionRequiresObservable: true,
  enforceActions: 'always',
})

const appStore = new AppStore()

ng.module('application').constant('store', appStore)
