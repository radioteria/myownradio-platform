import { action, computed, makeObservable, observable } from 'mobx'
import makeDebug from 'debug'
import { playAudio, stopAudio } from './RadioPlayerStore.util'

const debug = makeDebug('RadioPlayerStore')

export enum RadioPlayerStatus {
  Stopped = 'Stopped',
  Playing = 'Playing',
}

export type RadioPlayerState =
  | {
      status: RadioPlayerStatus.Stopped
    }
  | {
      status: RadioPlayerStatus.Playing
      src: string
      id: number
    }

export class RadioPlayerStore {
  private readonly htmlPlayerElement: HTMLAudioElement

  @observable.ref public state: RadioPlayerState = {
    status: RadioPlayerStatus.Stopped,
  }

  @action private setState = (state: RadioPlayerState) => {
    this.state = state
  }

  @observable bufferingStatus: null | 'buffering' | 'playing' = null

  @action private setBufferingStatus = (status: 'buffering' | 'playing') => {
    this.bufferingStatus = status
  }

  @observable bufferedAmount: number = 0

  @action private setBufferedAmount = (bufferedAmount: number) => {
    this.bufferedAmount = bufferedAmount
  }

  @observable public currentTime: number = 0

  @action private setCurrentTime = (currentTime: number) => {
    this.currentTime = currentTime
  }

  @computed public get src(): null | string {
    if (this.state.status === RadioPlayerStatus.Playing) {
      return this.state.src
    }

    return null
  }

  @computed public get id(): null | number {
    if (this.state.status === RadioPlayerStatus.Playing) {
      return this.state.id
    }

    return null
  }

  @computed public get isPlaying(): boolean {
    return this.state.status === RadioPlayerStatus.Playing
  }

  @computed public get isBuffering(): boolean {
    return this.bufferingStatus === 'buffering'
  }

  public constructor() {
    makeObservable(this)

    const audio = document.createElement('audio')

    audio.controls = false
    audio.autoplay = false
    audio.onwaiting = () => this.setBufferingStatus('buffering')
    audio.onplaying = () => this.setBufferingStatus('playing')
    audio.onprogress = () => {
      this.setCurrentTime(audio.currentTime)

      if (audio.buffered.length > 0) {
        this.setBufferedAmount(audio.buffered.end(audio.buffered.length - 1))
      }
    }

    this.htmlPlayerElement = audio
  }

  public play(id: number, format: string) {
    const src = `/flow?s=${id}&f=${format}`

    this.setState({
      status: RadioPlayerStatus.Playing,
      src,
      id,
    })

    playAudio(this.htmlPlayerElement, src)
  }

  public stop() {
    this.setState({
      status: RadioPlayerStatus.Stopped,
    })

    stopAudio(this.htmlPlayerElement)
  }
}
