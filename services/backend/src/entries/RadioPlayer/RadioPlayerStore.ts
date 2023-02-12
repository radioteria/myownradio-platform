import { action, computed, makeObservable, observable, reaction } from 'mobx'
import makeDebug from 'debug'
import { playAudio, playMediaSource, stopAudio } from './RadioPlayerStore.util'
import { IcyDemuxer } from './IcyDemuxer'

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

  @observable objectURL: null | string = null
  @action private setObjectURL(url: null | string) {
    this.objectURL = url
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

    reaction(
      () => this.src,
      (src, prevSrc) => {
        if (prevSrc) {
          URL.revokeObjectURL(prevSrc)
        }

        if (src) {
          const mediaSource = this.makeMediaSource(src)
          const url = URL.createObjectURL(mediaSource)
          this.setObjectURL(url)
        } else {
          this.setObjectURL(null)
        }
      },
    )

    this.htmlPlayerElement = audio
  }

  public play(id: number, format: string) {
    const src = `/flow?s=${id}&f=${format}`

    this.setState({
      status: RadioPlayerStatus.Playing,
      src,
      id,
    })

    if (this.objectURL) {
      playAudio(this.htmlPlayerElement, this.objectURL)
    }
  }

  public stop() {
    this.setState({
      status: RadioPlayerStatus.Stopped,
    })

    stopAudio(this.htmlPlayerElement)
  }

  private makeMediaSource(url: string): MediaSource {
    const mediaSource = new MediaSource()

    mediaSource.addEventListener('sourceopen', async () => {
      const response = await window.fetch(url, {
        headers: {
          'icy-metadata': '1',
        },
      })
      const icyMetaInterval = parseInt(response.headers.get('icy-metaint') ?? '0', 10)
      const reader = response.body ?? new ReadableStream()
      const demuxedReader = new IcyDemuxer(reader, icyMetaInterval).getReader()

      const sourceBuffer = mediaSource.addSourceBuffer(
        response.headers.get('Content-Type') ?? 'audio/mpeg',
      )
      mediaSource.addEventListener('sourceclose', () => demuxedReader.cancel())

      while (true) {
        const { done, value } = await demuxedReader.read()

        if (mediaSource.readyState !== 'open') {
          break
        }

        if (done) {
          sourceBuffer.appendBuffer(new Uint8Array())
          mediaSource.endOfStream()
          break
        }

        sourceBuffer.appendBuffer(value)

        await new Promise((resolve, reject) => {
          sourceBuffer.onupdateend = () => resolve(null)
          sourceBuffer.onerror = (error) => reject(error)
        })
      }
    })

    return mediaSource
  }
}
