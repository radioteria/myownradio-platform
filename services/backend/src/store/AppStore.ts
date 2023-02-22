import { RadioPlayerStore } from '../entries/RadioPlayer'
import { Channel, PlayFormat } from '../models'

export class AppStore {
  readonly radioPlayerStore: RadioPlayerStore = new RadioPlayerStore()

  public playChannel(rawChannel: unknown, rawFormat: unknown) {
    const channel = Channel.parse(rawChannel)
    const format = PlayFormat.parse(rawFormat)

    this.radioPlayerStore.playChannel(channel, format)
  }

  public stopChannel() {
    this.radioPlayerStore.stop()
  }
}
