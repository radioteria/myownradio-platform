import { action, makeObservable, observable } from 'mobx'

export enum PlayerStatus {
  Stopped = 'Stopped',
}

export type PlayerState = {
  status: PlayerStatus.Stopped
}

export class PlayerStore {
  @observable.ref
  public state = {
    status: PlayerStatus.Stopped,
  }

  @action private setState = (state: PlayerState) => {
    this.state = state
  }

  public constructor() {
    makeObservable(this)
  }
}
