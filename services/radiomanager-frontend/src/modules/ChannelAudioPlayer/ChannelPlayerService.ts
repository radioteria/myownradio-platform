import makeDebug from 'debug'
import { AudioFormat, getNowPlaying, getTrackTranscodeStream } from '@/api'
import { appendBufferAsync, playAudio, stopAudio } from '@/utils/audio'
import { streamAsyncIterator } from '@/utils/iterators'
import { getWorldTime } from '@/api/time'
import { getSupportedAudioFormats, makeBufferTransform } from './probe'
import { createWebMTimeCodeParser } from '@/media/webm'
import { Clock } from '@/media/clock'

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
    const abortController = new AbortController()

    mediaSource.addEventListener('sourceclose', () => {
      localDebug('Media Source closed')
      abortController.abort()
    })

    mediaSource.addEventListener('sourceopen', async () => {
      localDebug('Opening Media Source')

      const startTimeMillis = performance.now()
      const { unixtime } = await getWorldTime()
      const clock = new Clock(startTimeMillis)

      let currentTime = unixtime * 1000 - Math.floor(performance.now() - startTimeMillis)

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
        const audioFormat = this.supportedAudioFormats.opus
          ? AudioFormat.Opus
          : this.supportedAudioFormats.vorbis
          ? AudioFormat.Vorbis
          : null

        const { stream, contentType } = await getTrackTranscodeStream(
          nowPlaying.currentTrack.trackId,
          nowPlaying.currentTrack.offset,
          audioFormat,
          abortController.signal,
        )

        if (!contentType.includes('audio/webm')) {
          throw new TypeError(`${contentType} is not supported!`)
        }

        const parsedStream = stream
          .pipeThrough(makeBufferTransform(4096))
          .pipeThrough(createWebMTimeCodeParser())

        if (!sourceBuffer) {
          localDebug('Creating Source Buffer')
          sourceBuffer = mediaSource.addSourceBuffer(contentType)
          localDebug('Source Buffer Created')
        }

        const sb = sourceBuffer

        let lastTimestamp = 0

        clock.resetPts()

        try {
          for await (const [bytes, timestamp] of streamAsyncIterator(
            parsedStream,
            abortController.signal,
          )) {
            clock.advanceTimeByPts(timestamp)

            if (mediaSource.readyState !== 'open') {
              localDebug('MediaSource closed: exiting')
              return
            }

            await appendBufferAsync(sb, new Uint8Array(bytes))

            const currentTimeMillis = performance.now()

            await clock.sync(currentTimeMillis + BUFFER_TIME_MILLIS)

            lastTimestamp = timestamp
          }
        } finally {
          await parsedStream.cancel()
        }

        currentTime += remainder

        if (mediaSource.readyState === 'open') {
          sourceBuffer.timestampOffset += lastTimestamp / 1000
        }
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
