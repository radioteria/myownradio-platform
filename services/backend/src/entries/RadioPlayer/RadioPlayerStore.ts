import { action, computed, makeObservable, observable, runInAction } from 'mobx'
import makeDebug from 'debug'
import { appendBufferAsync, playAudio, stopAudio } from './RadioPlayerStore.util'
import { makeIcyDemuxedStream, streamAsyncIterator } from './IcyDemuxer.utils'
import { Channel, decodeIcyMetadata, IcyMetadata, PlayFormat } from '../../models'

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
      channel: Channel
      format: PlayFormat
      objectURL: string
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

  @computed public get playingChannelId(): null | number {
    if (this.state.status === RadioPlayerStatus.Playing) {
      return this.state.channel.sid
    }

    return null
  }

  @computed public get playingChannel(): Channel | null {
    if (this.state.status === RadioPlayerStatus.Playing) {
      return this.state.channel
    }

    return null
  }

  @computed public get streamTitle(): null | string {
    return this.metadata?.stream_title ?? null
  }

  @computed public get isPlaying(): boolean {
    return this.state.status === RadioPlayerStatus.Playing
  }

  @computed public get isBuffering(): boolean {
    return this.bufferingStatus === 'buffering'
  }

  @observable.ref private metadata: null | IcyMetadata = null
  @action private setMetadata(metadata: IcyMetadata | null) {
    this.metadata = metadata
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
    audio.onended = () => {
      if (
        this.state.status === RadioPlayerStatus.Playing &&
        this.state.objectURL === this.htmlPlayerElement.src
      ) {
        debug('Playback unexpectedly ended: restarting')

        this.play(this.state.channel, this.state.format)
      }
    }

    this.htmlPlayerElement = audio
  }

  public play(channel: Channel, format: PlayFormat) {
    const src = `/flow?s=${channel.sid}&f=${format}`

    const mediaSource = this.makeMediaSource(src)
    const objectURL = URL.createObjectURL(mediaSource)

    if (this.state.status === RadioPlayerStatus.Playing) {
      URL.revokeObjectURL(this.state.objectURL)
    }

    this.setState({
      status: RadioPlayerStatus.Playing,
      channel,
      format,
      objectURL,
    })

    playAudio(this.htmlPlayerElement, objectURL)
  }

  public stop() {
    if (this.state.status === RadioPlayerStatus.Playing) {
      URL.revokeObjectURL(this.state.objectURL)
    }

    runInAction(() => {
      this.setState({
        status: RadioPlayerStatus.Stopped,
      })

      this.setMetadata(null)
    })

    stopAudio(this.htmlPlayerElement)
  }

  private makeMediaSource(url: string): MediaSource {
    const localDebug = debug.extend('MediaSource')
    const mediaSource = new MediaSource()
    const abortController = new AbortController()

    mediaSource.addEventListener('sourceclose', () => {
      abortController.abort()
    })

    mediaSource.addEventListener('sourceopen', async () => {
      const [mediaStream, metadataStream, contentType] = await makeIcyDemuxedStream(
        url,
        abortController.signal,
      )
      const sourceBuffer = mediaSource.addSourceBuffer(contentType)

      const metadataLoop = async (signal: AbortSignal) => {
        localDebug('Starting metadata loop')
        try {
          for await (const rawMetadata of streamAsyncIterator(metadataStream, signal)) {
            localDebug('Received metadata: %s', rawMetadata)
            this.setMetadata(decodeIcyMetadata(rawMetadata))
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
        await Promise.race([metadataLoopPromise, mediaLoopPromise])
      } finally {
        localDebug('Some or all of MediaSource loops was interrupted')
        abortController.abort()
        await Promise.all([metadataLoopPromise, mediaLoopPromise])
      }
    })

    return mediaSource
  }
}
