import { action, computed, makeObservable, observable, reaction } from 'mobx'
import makeDebug from 'debug'
import { appendBufferAsync, playAudio, playMediaSource, stopAudio } from './RadioPlayerStore.util'
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
    const localDebug = debug.extend('MediaSource')

    mediaSource.addEventListener('sourceopen', async () => {
      const abortController = new AbortController()

      const [mediaStream, metadataStream, contentType] = await makeIcyDemuxedStream(
        url,
        abortController.signal,
      )
      const sourceBuffer = mediaSource.addSourceBuffer(contentType)

      const metadataLoop = async (signal: AbortSignal) => {
        localDebug('Starting metadata loop')
        try {
          for await (const metadata of streamAsyncIterator(metadataStream, signal)) {
            localDebug('Received metadata: %s', metadata)
            this.setMetadata(metadata)
          }
          localDebug('Cleanup metadata')
          this.setMetadata(null)
        } finally {
          localDebug('End of metadata loop: closing stream')
          await metadataStream.cancel()
        }
      }

      const mediaLoop = async (signal: AbortSignal) => {
        localDebug('Starting media loop')
        try {
          for await (const bytes of streamAsyncIterator(mediaStream, signal)) {
            if (mediaSource.readyState !== 'open') {
              localDebug('MediaSource closed: exiting')
              return
            }

            await appendBufferAsync(sourceBuffer, bytes)
          }

          if (mediaSource.readyState !== 'open') {
            localDebug('MediaSource closed: exiting')
            return
          }

          localDebug('Media stream completed: ending MediaSource')

          await appendBufferAsync(sourceBuffer, new Uint8Array())

          mediaSource.endOfStream()
        } finally {
          localDebug('End of media loop: closing stream')
          await mediaStream.cancel()
        }
      }

      const metadataLoopPromise = metadataLoop(abortController.signal)
      const mediaLoopPromise = mediaLoop(abortController.signal)

      try {
        localDebug('Starting MediaSource loops')
        await Promise.any([metadataLoopPromise, mediaLoopPromise])
      } finally {
        localDebug('Some or all of MediaSource loops was interrupted')
        abortController.abort()
        await Promise.all([metadataLoopPromise, mediaLoopPromise])
      }
    })

    return mediaSource
  }
}
