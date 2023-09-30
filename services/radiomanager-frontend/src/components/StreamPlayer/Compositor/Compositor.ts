import makeDebug from 'debug'
import { appendBuffer } from '@/utils/audio'
import { getWorldTime } from '@/api/time'
import { AudioFormat, getNowPlaying, getTrackTranscodeStream } from '@/api'
import { sleep } from '@/utils/sleep'
import { makeChunkTransform } from './BufferUtils'

const debug = makeDebug('compositor')

interface Options {
  readonly bufferAheadTime: number
  readonly supportedCodecs: {
    readonly opus: boolean
    readonly vorbis: boolean
  }
}

const PRODUCED_STREAM_CHUNK_SIZE = 8192

export const composeStreamMediaSource = (channelId: number, opts: Options) => {
  const { supportedCodecs, bufferAheadTime } = opts

  const mediaSource = new MediaSource()
  const abortController = new AbortController()

  const format = supportedCodecs.opus
    ? AudioFormat.Opus
    : supportedCodecs.vorbis
    ? AudioFormat.Vorbis
    : null

  mediaSource.addEventListener('sourceclose', () => {
    debug('Media Source closed')
    abortController.abort()
  })

  mediaSource.addEventListener('sourceopen', async () => {
    debug('MediaSource opened')

    let sourceBuffer: SourceBuffer | null = null

    try {
      const startTimeMillis = performance.now()
      const { unixtime: startUnixtime } = await getWorldTime()

      let streamTimeMillis = startUnixtime * 1000

      while (true) {
        const nowPlaying = await getNowPlaying(channelId, streamTimeMillis)
        const remainder = nowPlaying.currentTrack.duration - nowPlaying.currentTrack.offset
        debug(
          'Now Playing: %s (pos: %d, rem: %d)',
          nowPlaying.currentTrack.trackId,
          nowPlaying.currentTrack.offset,
          remainder,
        )
        const { stream, contentType } = await getTrackTranscodeStream(
          nowPlaying.currentTrack.trackId,
          nowPlaying.currentTrack.offset,
          format,
          abortController.signal,
        )
        const reader = stream
          .pipeThrough(makeChunkTransform(PRODUCED_STREAM_CHUNK_SIZE), {
            signal: abortController.signal,
          })
          .getReader()

        if (sourceBuffer === null) {
          sourceBuffer = mediaSource.addSourceBuffer(contentType)
        }

        try {
          while (true) {
            const { value, done } = await reader.read()

            if (mediaSource.readyState !== 'open') {
              abortController.abort(new Error('PIPE'))
              return
            }

            if (done) {
              sourceBuffer.timestampOffset = sourceBuffer.buffered.end(
                sourceBuffer.buffered.length - 1,
              )
              break
            }

            await appendBuffer(sourceBuffer, value)

            const bufferedTime = sourceBuffer.buffered.end(sourceBuffer.buffered.length - 1) * 1000
            const estimatedTime = performance.now() - startTimeMillis

            await sleep(bufferedTime - estimatedTime - bufferAheadTime)
          }

          streamTimeMillis += remainder
        } catch (e) {
          await reader.cancel(e)
        }
      }
    } catch (e) {
      if (e instanceof Error && e.name === 'AbortError') {
        return
      }

      debug('Media stream composing failed: %s', e)
      mediaSource.endOfStream()
    }
  })

  return mediaSource
}
