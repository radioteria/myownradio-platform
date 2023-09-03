import { BACKEND_BASE_URL, getNowPlaying } from '@/api'
import makeDebug from 'debug'
import { loadAudio, playAudio, stopAudio } from '@/utils/audio'
import EventEmitter from 'events'

const debug = makeDebug('ChannelPlayerService')

export class ChannelPlayerService {
  private currentTimestamp = Date.now()
  private stopping = false

  private nextAudioSrcCache: null | string = null

  constructor(
    private readonly channelId: number,
    private readonly audioElement: HTMLAudioElement,
  ) {}

  public readonly runLoop = async () => {
    debug('Starting player loop')
    while (!this.stopping) {
      let nowPlaying = await getNowPlaying(this.channelId, this.currentTimestamp)
      const { trackId, offset, duration } = nowPlaying.currentTrack
      debug('Now playing: %s (%d)', trackId, offset)

      const audioUrl = new URL(
        `${BACKEND_BASE_URL}/radio-manager/api/v0/tracks/${trackId}/transcode`,
      )
      if (offset > 0) audioUrl.searchParams.set('initialPosition', `${offset}`)
      const audioSrc = audioUrl.toString()
      debug('Audio URL: %s', audioSrc)

      playAudio(this.audioElement, this.nextAudioSrcCache ?? audioSrc)

      this.nextAudioSrcCache = null

      await new Promise((resolve, reject) => {
        const handleError = (ev: ErrorEvent) => {
          debug('Stopping playback due to error: %s', ev)
          reject(ev)
          dispose()
        }

        const handleEnded = () => {
          debug('Playback ended')
          resolve(null)
          dispose()
        }

        const handleCanPlayThrough = () => {
          this.nextAudioSrcCache = `${BACKEND_BASE_URL}/radio-manager/api/v0/tracks/${nowPlaying.nextTrack.trackId}/transcode`
        }

        this.audioElement.addEventListener('error', handleError)
        this.audioElement.addEventListener('ended', handleEnded)
        this.audioElement.addEventListener('canplaythrough', handleCanPlayThrough)

        const dispose = () => {
          this.audioElement.removeEventListener('error', handleError)
          this.audioElement.removeEventListener('ended', handleEnded)
          this.audioElement.removeEventListener('canplaythrough', handleCanPlayThrough)
        }
      })

      this.currentTimestamp += duration
    }

    debug('Playback loop ended')
  }

  public readonly reload = () => {
    this.nextAudioSrcCache = null

    stopAudio(this.audioElement)
  }

  public readonly stop = () => {
    this.stopping = true

    stopAudio(this.audioElement)
  }
}
