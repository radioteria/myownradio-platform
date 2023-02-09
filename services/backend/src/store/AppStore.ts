import { RadioPlayerStore } from '../entries/RadioPlayer'

export class AppStore {
  readonly audioPlayerStore: RadioPlayerStore = new RadioPlayerStore()
}
