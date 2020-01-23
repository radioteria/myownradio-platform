import { EventEmitter } from 'events';

export class MetadataEmitter extends EventEmitter {
  private currentTitle: string = '';

  public changeTitle(newTitle: string) {
    this.currentTitle = newTitle;
    this.emit('title-changed', newTitle)
  }

  public getCurrentTitle(): string {
    return this.currentTitle;
  }
}
