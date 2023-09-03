import { BACKEND_BASE_URL, getNowPlaying, NowPlaying } from '@/api'
import makeDebug from 'debug'
import { loadAudio, playAudio, stopAudio } from '@/utils/audio'

const debug = makeDebug('ChannelPlayerService')

export class ChannelPlayerService {
  private currentTimestamp = Date.now()
  private stopping = false

  private activeAudioElement = 0
  private nextNowPlayingPromise: null | Promise<NowPlaying> = null

  constructor(
    private readonly channelId: number,
    private readonly audio0Element: HTMLAudioElement,
    private readonly audio1Element: HTMLAudioElement,
  ) {}

  private readonly getNextActiveAudioElement = () => {
    this.activeAudioElement = 1 - this.activeAudioElement

    return this.activeAudioElement === 0 ? this.audio0Element : this.audio1Element
  }

  private readonly getInactiveAudioElement = () => {
    return this.activeAudioElement === 0 ? this.audio1Element : this.audio0Element
  }

  public readonly runLoop = async () => {
    debug('Starting player loop')

    while (!this.stopping) {
      let nowPlaying = this.nextNowPlayingPromise
        ? await this.nextNowPlayingPromise
        : await getNowPlaying(this.channelId, this.currentTimestamp)
      this.nextNowPlayingPromise = null

      const { trackId, offset, duration } = nowPlaying.currentTrack
      const remainder = duration - offset
      debug('Now playing: %s (%d)', trackId, offset)

      const audioUrl = new URL(
        `${BACKEND_BASE_URL}/radio-manager/api/v0/tracks/${trackId}/transcode`,
      )
      if (offset > 0) audioUrl.searchParams.set('initialPosition', `${offset}`)
      const audioSrc = audioUrl.toString()
      debug('Audio URL: %s', audioSrc)

      const activeAudioElement = this.getNextActiveAudioElement()

      playAudio(activeAudioElement, audioSrc)

      debug('Current latency: %dms', Date.now() - this.currentTimestamp)

      this.currentTimestamp += remainder

      this.nextNowPlayingPromise = getNowPlaying(this.channelId, this.currentTimestamp)

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
          const nextAudioSrc = `${BACKEND_BASE_URL}/radio-manager/api/v0/tracks/${nowPlaying.nextTrack.trackId}/transcode`
          loadAudio(this.getInactiveAudioElement(), nextAudioSrc)
        }

        activeAudioElement.addEventListener('error', handleError)
        activeAudioElement.addEventListener('ended', handleEnded)
        activeAudioElement.addEventListener('canplaythrough', handleCanPlayThrough)

        const dispose = () => {
          activeAudioElement.removeEventListener('error', handleError)
          activeAudioElement.removeEventListener('ended', handleEnded)
          activeAudioElement.removeEventListener('canplaythrough', handleCanPlayThrough)
        }
      })
    }

    debug('Playback loop ended')
  }

  public readonly reload = () => {
    this.nextNowPlayingPromise = null

    stopAudio(this.audio0Element)
    stopAudio(this.audio1Element)
  }

  public readonly stop = () => {
    this.stopping = true

    stopAudio(this.audio0Element)
    stopAudio(this.audio1Element)
  }
}
