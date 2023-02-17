import { action, computed, makeObservable, observable, reaction } from 'mobx'
import makeDebug from 'debug'
import { playAudio, playMediaSource, stopAudio } from './RadioPlayerStore.util'
import { makeIcyDemuxer } from './IcyDemuxer'
import { makeIcyDemuxedStream, streamAsyncIterator } from './IcyDemuxer.utils'

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

  @observable metadata: null | string = null
  @action private setMetadata(metadata: null | string) {
    this.metadata =
      metadata?.replaceAll('\0', '').replace("StreamTitle='", '').replace("';", '') ?? null
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

    this.setMetadata(null)
  }

  private makeMediaSource(url: string): MediaSource {
    const mediaSource = new MediaSource()

    mediaSource.addEventListener('sourceopen', async () => {
      const [mediaStream, metadataStream, contentType] = await makeIcyDemuxedStream(url)

      const metadataLoop = async () => {
        for await (const metadata of streamAsyncIterator(metadataStream)) {
          this.setMetadata(metadata)
        }
        this.setMetadata(null)
      }

      const mediaLoop = async () => {
        const sourceBuffer = mediaSource.addSourceBuffer(contentType)

        for await (const bytes of streamAsyncIterator(mediaStream)) {
          if (mediaSource.readyState !== 'open') {
            return
          }

          sourceBuffer.appendBuffer(bytes)

          await new Promise((resolve, reject) => {
            sourceBuffer.onupdateend = () => resolve(null)
            sourceBuffer.onerror = (error) => reject(error)
          })
        }

        sourceBuffer.appendBuffer(new Uint8Array())
        mediaSource.endOfStream()
      }

      await Promise.race([metadataLoop(), mediaLoop()]).finally(() =>
        Promise.all([metadataStream.cancel(), mediaStream.cancel()]),
      )
    })

    return mediaSource
  }
}
