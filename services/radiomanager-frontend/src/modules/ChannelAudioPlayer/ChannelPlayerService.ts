import { getNowPlaying, getTrackTranscodeStream } from '@/api'
import makeDebug from 'debug'
import { appendBufferAsync, playAudio, stopAudio } from '@/utils/audio'
import { streamAsyncIterator } from '@/utils/iterators'

const debug = makeDebug('ChannelPlayerService')

const BUFFER_LENGTH_MILLISECONDS = 5_000

export class ChannelPlayerService {
  private stopping = false

  private bufferedTimeInternal = 0

  public get bufferedTime() {
    return this.bufferedTimeInternal
  }

  constructor(
    private readonly channelId: number,
    private readonly audioElement: HTMLAudioElement,
  ) {}

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
    const abortController = new AbortController()

    const startTimeMillis = Date.now()
    let currentTime = startTimeMillis

    mediaSource.addEventListener('sourceclose', () => {
      localDebug('Media Source closed')
      abortController.abort()
    })

    mediaSource.addEventListener('sourceopen', async () => {
      localDebug('Opening Media Source')

      let sourceBuffer = <SourceBuffer | null>null

      while (!this.stopping) {
        const nowPlaying = await getNowPlaying(this.channelId, currentTime)
        const remainder = nowPlaying.currentTrack.duration - nowPlaying.currentTrack.offset
        localDebug(
          'Now Playing: %s (pos: %d, rem: %d)',
          nowPlaying.currentTrack.trackId,
          nowPlaying.currentTrack.offset,
          remainder,
        )
        const { stream, contentType } = await getTrackTranscodeStream(
          nowPlaying.currentTrack.trackId,
          nowPlaying.currentTrack.offset,
          abortController.signal,
        )

        if (!sourceBuffer) {
          localDebug('Creating Source Buffer')
          sourceBuffer = mediaSource.addSourceBuffer(contentType)
        }

        const sb = sourceBuffer

        try {
          for await (const bytes of streamAsyncIterator(stream, abortController.signal)) {
            if (mediaSource.readyState !== 'open') {
              localDebug('MediaSource closed: exiting')
              return
            }

            await appendBufferAsync(sb, bytes)

            const currentTimeMillis = Date.now()
            const estimatedTimestampMillis =
              currentTimeMillis - startTimeMillis + BUFFER_LENGTH_MILLISECONDS
            const bufferTimestampOffsetMillis = sb.timestampOffset * 1000

            await new Promise<void>((resolve) => {
              window.setTimeout(
                () => resolve(),
                bufferTimestampOffsetMillis - estimatedTimestampMillis,
              )
            })
          }
        } finally {
          await stream.cancel()
        }

        currentTime += remainder
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
