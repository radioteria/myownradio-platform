import makeDebug from 'debug'
import { appendBufferAsync, playAudio, stopAudio } from '@/utils/audio'
import { streamAsyncIterator } from '@/utils/iterators'
import { getSupportedAudioFormats, makeBufferTransform } from './probe'
import { composeChannelWebmStream, EventType } from '@/player/webmStreamCompositor'

const debug = makeDebug('ChannelPlayerService')

const BUFFER_TIME_MILLIS = 2_000

export class ChannelPlayerService {
  private stopping = false

  private bufferedTimeInternal = 0

  public get bufferedTime() {
    return this.bufferedTimeInternal
  }

  constructor(
    private readonly channelId: number,
    private readonly audioElement: HTMLAudioElement,
    private readonly supportedAudioFormats = getSupportedAudioFormats(),
  ) {
    debug('Supported audio formats', this.supportedAudioFormats)
  }

  public readonly runLoop = async () => {
    while (!this.stopping) {
      let objectURL: string | null = null

      try {
        debug('Creating Media Source')
        const mediaSource = this.createChannelMediaSource()
        objectURL = URL.createObjectURL(mediaSource)

        debug('Playing Media Source')
        playAudio(this.audioElement, objectURL)

        await new Promise<void>((resolve, reject) => {
          const handleEnded = () => {
            resolve()
            dispose()
          }

          const handleError = (ev: ErrorEvent) => {
            reject(ev)
            dispose()
          }

          const handleTimeUpdate = () => {
            if (this.audioElement.buffered.length > 0) {
              const end = this.audioElement.buffered.end(0)
              const position = this.audioElement.currentTime

              this.bufferedTimeInternal = end - position
            }
          }

          this.audioElement.addEventListener('ended', handleEnded)
          this.audioElement.addEventListener('error', handleError)
          this.audioElement.addEventListener('timeupdate', handleTimeUpdate)

          const dispose = () => {
            this.audioElement.removeEventListener('ended', handleEnded)
            this.audioElement.removeEventListener('error', handleError)
            this.audioElement.removeEventListener('timeupdate', handleTimeUpdate)
            this.bufferedTimeInternal = 0
          }
        })

        debug('Media Source playback finished')
      } finally {
        objectURL && URL.revokeObjectURL(objectURL)
      }
    }
  }

  public readonly createChannelMediaSource = (): MediaSource => {
    const localDebug = debug.extend('MediaSource')
    const mediaSource = new MediaSource()

    let abortController: AbortController | null = null

    mediaSource.addEventListener('sourceclose', () => {
      localDebug('Media Source closed')
      abortController?.abort()
      abortController = null
    })

    mediaSource.addEventListener('sourceopen', async () => {
      if (abortController) {
        throw new Error('Media Source already opened!')
      }

      abortController = new AbortController()

      localDebug('Opening Media Source')

      const stream = composeChannelWebmStream(this.channelId, {
        bufferAheadMillis: BUFFER_TIME_MILLIS,
        supportedCodecs: this.supportedAudioFormats,
      })

      let sourceBuffer: SourceBuffer | null = null

      try {
        for await (const streamEvent of streamAsyncIterator(stream, abortController.signal)) {
          if (mediaSource.readyState !== 'open') {
            localDebug('MediaSource closed: exiting')
            return
          }

          switch (streamEvent.eventType) {
            case EventType.Segment: {
              if (!sourceBuffer) {
                localDebug('Creating Source Buffer', {
                  contentType: streamEvent.contentType,
                })
                sourceBuffer = mediaSource.addSourceBuffer(streamEvent.contentType)
              }
              sourceBuffer.timestampOffset = streamEvent.timestampOffset / 1000
              debug('Update Source Buffer timestamp offset = %f', sourceBuffer.timestampOffset)
              break
            }

            case EventType.Buffer: {
              if (sourceBuffer) {
                await appendBufferAsync(sourceBuffer, streamEvent.buffer)
              }
              break
            }
            default:
          }
        }
      } finally {
        await stream.cancel()
      }

      if (mediaSource.readyState !== 'open') {
        localDebug('MediaSource closed: exiting')
        return
      }

      if (sourceBuffer) {
        await appendBufferAsync(sourceBuffer, new Uint8Array())
      }

      mediaSource.endOfStream()
    })

    return mediaSource
  }

  public readonly reload = () => {
    debug('Reloading channel')
    stopAudio(this.audioElement)
  }

  public readonly stop = () => {
    debug('Stopping channel playback')
    this.stopping = true

    stopAudio(this.audioElement)
  }
}
