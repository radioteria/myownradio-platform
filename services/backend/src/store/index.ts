import { PlayerStore } from './player/playerStore'

export class AppStore {
  private readonly playerStore: PlayerStore = new PlayerStore()
}
