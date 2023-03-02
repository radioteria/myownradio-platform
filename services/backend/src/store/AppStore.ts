import { RadioPlayerStore } from '../entries/RadioPlayer'
import { Channel, PlayFormat } from '../models'
import { RadioPlayerState } from '../entries/RadioPlayer/RadioPlayerStore'
import { action, computed } from 'mobx'

export class AppStore {
  readonly radioPlayerStore: RadioPlayerStore = new RadioPlayerStore()

  @computed public get isRadioPlayerBuffering(): boolean {
    return this.radioPlayerStore.isBuffering
  }

  @computed public get isRadioPlayerPlaying(): boolean {
    return this.radioPlayerStore.isPlaying
  }

  @computed public get trackTitle(): string | null {
    return this.radioPlayerStore.trackTitle
  }

  @computed public get playingChannelId(): number | null {
    return this.radioPlayerStore.playingChannelId
  }

  @computed public get playingChannel(): Channel | null {
    return this.radioPlayerStore.playingChannel
  }

  @computed public get radioPlayerState(): RadioPlayerState {
    return this.radioPlayerStore.state
  }

  @action public playChannel(rawChannel: unknown, rawFormat: unknown) {
    const channel = Channel.parse(rawChannel)
    const format = PlayFormat.parse(rawFormat)

    this.radioPlayerStore.play(channel, format)
  }

  @action public stopChannel() {
    this.radioPlayerStore.stop()
  }
}
