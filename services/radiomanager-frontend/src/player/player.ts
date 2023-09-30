import makeDebug from 'debug'
import { playAudio, stopAudio } from '@/utils/audio'
import { composeStreamMediaSource } from '@/components/StreamPlayer/Compositor/Compositor'
import { browserFeatures } from '@/features'

const debug = makeDebug('Player')
const BUFFER_AHEAD_TIME = 5_000 // 5 seconds

export class Player {
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
        const mediaSource = composeStreamMediaSource(this.channelId, {
          bufferAheadTime: BUFFER_AHEAD_TIME,
          supportedCodecs: browserFeatures.supportedAudioCodecs,
        })
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

  public readonly reload = () => {
    debug('Reloading Channel')
    stopAudio(this.audioElement)
  }

  public readonly stop = () => {
    debug('Stopping Channel Playback')
    this.stopping = true
    stopAudio(this.audioElement)
  }
}
