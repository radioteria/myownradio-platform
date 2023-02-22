import { RadioPlayerStore } from '../entries/RadioPlayer'
import { Channel, PlayFormat } from '../models'
import { RadioPlayerState } from '../entries/RadioPlayer/RadioPlayerStore'

export class AppStore {
  readonly radioPlayerStore: RadioPlayerStore = new RadioPlayerStore()

  public get isBuffering(): boolean {
    return this.radioPlayerStore.isBuffering
  }

  public get isPlaying(): boolean {
    return this.radioPlayerStore.isPlaying
  }

  public get trackTitle(): string | null {
    return this.radioPlayerStore.streamTitle
  }

  public get playingChannelId(): number | null {
    return this.radioPlayerStore.playingChannelId
  }

  public get playingChannel(): Channel | null {
    return this.radioPlayerStore.playingChannel
  }

  public get radioPlayerState(): RadioPlayerState {
    return this.radioPlayerStore.state
  }

  public playChannel(rawChannel: unknown, rawFormat: unknown) {
    const channel = Channel.parse(rawChannel)
    const format = PlayFormat.parse(rawFormat)

    this.radioPlayerStore.play(channel, format)
  }

  public stopChannelPlayer() {
    this.radioPlayerStore.stop()
  }
}
