import makeDebug from 'debug'
import { getWorldTime } from '@/api/time'
import { AudioFormat, getNowPlaying, getTrackTranscodeStream } from '@/api'
import { makeBufferTransform } from '@/modules/ChannelAudioPlayer/probe'
import { StreamClock } from './streamClock'
import { createWebmTimeParser } from './webmTimeParser'
import { streamAsyncIterator } from '@/utils/iterators'

export enum EventType {
  Buffer,
  Segment,
}

type StreamEvent =
  | {
      readonly eventType: EventType.Buffer
      readonly buffer: Uint8Array
      readonly timestamp: number
    }
  | {
      readonly eventType: EventType.Segment
      readonly timestampOffset: number
      readonly title: string
      readonly contentType: string
    }

interface StreamOptions {
  readonly bufferAheadMillis: number
  readonly supportedCodecs: {
    readonly opus: boolean
    readonly vorbis: boolean
  }
}

export const composeChannelWebmStream = (channelId: number, opts: StreamOptions) => {
  const debug = makeDebug('composeChannelWebmStream')

  let abortController: AbortController | null = null
  let isStopping = false

  const { supportedCodecs, bufferAheadMillis } = opts

  return new ReadableStream<StreamEvent>({
    cancel() {
      debug('Stopping stream')
      isStopping = true
      abortController?.abort()
    },
    async start(controller) {
      abortController = new AbortController()
      isStopping = false

      const startTimeMillis = performance.now()
      const { unixtime } = await getWorldTime()
      const clock = new StreamClock(startTimeMillis)

      let currentTime = unixtime * 1000 - Math.floor(performance.now() - startTimeMillis)
      let timestampOffset = 0

      while (!isStopping) {
        const nowPlaying = await getNowPlaying(channelId, currentTime)
        const remainder = nowPlaying.currentTrack.duration - nowPlaying.currentTrack.offset
        debug(
          'Now Playing: %s (pos: %d, rem: %d)',
          nowPlaying.currentTrack.trackId,
          nowPlaying.currentTrack.offset,
          remainder,
        )
        const format = supportedCodecs.opus
          ? AudioFormat.Opus
          : supportedCodecs.vorbis
          ? AudioFormat.Vorbis
          : null

        const { stream, contentType } = await getTrackTranscodeStream(
          nowPlaying.currentTrack.trackId,
          nowPlaying.currentTrack.offset,
          format,
          abortController.signal,
        )

        if (!contentType.includes('audio/webm')) {
          controller.error(new TypeError(`${contentType} is not supported!`))
          return
        }

        const parsedStream = stream
          .pipeThrough(makeBufferTransform(4096))
          .pipeThrough(createWebmTimeParser())

        controller.enqueue({
          eventType: EventType.Segment,
          contentType,
          timestampOffset: timestampOffset,
          title: nowPlaying.currentTrack.title,
        })

        let lastTimestamp = 0

        clock.resetPts()

        try {
          for await (const [chunk, timestamp] of streamAsyncIterator(
            parsedStream,
            abortController.signal,
          )) {
            clock.advanceTimeByPts(timestamp)

            controller.enqueue({
              eventType: EventType.Buffer,
              buffer: chunk,
              timestamp,
            })

            const currentTimeMillis = performance.now()
            await clock.sync(currentTimeMillis + bufferAheadMillis, abortController.signal)

            lastTimestamp = timestamp
          }
        } catch (error) {
          if (error instanceof Error && error.name === 'AbortError') {
            break
          }

          throw error
        } finally {
          await parsedStream.cancel()
        }

        currentTime += remainder
        timestampOffset += lastTimestamp
      }

      controller.close()
    },
  })
}
