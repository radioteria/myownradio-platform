import { action, computed, makeObservable, observable } from 'mobx'

export enum PlayerStatus {
  Stopped = 'Stopped',
  Playing = 'Playing',
}

export type RadioPlayerState =
  | {
      status: PlayerStatus.Stopped
    }
  | {
      status: PlayerStatus.Playing
      src: string
      id: number
    }

export class RadioPlayerStore {
  @observable.ref public state: RadioPlayerState = {
    status: PlayerStatus.Stopped,
  }

  @action private setState = (state: RadioPlayerState) => {
    this.state = state
  }

  @computed public get src(): null | string {
    if (this.state.status === PlayerStatus.Playing) {
      return this.state.src
    }

    return null
  }

  @computed public get id(): null | number {
    if (this.state.status === PlayerStatus.Playing) {
      return this.state.id
    }

    return null
  }

  public constructor() {
    makeObservable(this)
  }

  @observable bufferingStatus: null | 'waiting' | 'playing' = null

  @action public setBufferingStatus = (status: 'waiting' | 'playing') => {
    this.bufferingStatus = status
  }

  @observable bufferedAmount: number = 0

  @action public setBufferedAmount = (bufferedAmount: number) => {
    this.bufferedAmount = bufferedAmount
  }

  @observable public currentTime: number = 0

  @action public setCurrentTime = (currentTime: number) => {
    this.currentTime = currentTime
  }

  public play(src: string, id: number) {
    this.setState({
      status: PlayerStatus.Playing,
      src,
      id,
    })
  }

  public stop() {
    this.setState({
      status: PlayerStatus.Stopped,
    })
  }
}
