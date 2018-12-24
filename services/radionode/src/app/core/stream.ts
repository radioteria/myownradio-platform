import { PassThrough } from 'stream';

import { module } from '../utils/log-utils';
import { decode, Decoder } from './decoder';
import { wrap } from '../utils/stream-utils';

import { IApiService, INowPlaying } from '../service/backend';

export default class Stream extends PassThrough {
  backendService: IApiService;
  channelId: string;
  decoder: Decoder;
  log = module(this);

  terminated: boolean = false;

  constructor(backendService: IApiService, channelId: string) {
    super();

    this.channelId = channelId;
    this.backendService = backendService;

    this.play();
    this.log('info', 'Initialized');
  }

  stop() {
    this.log('info', 'Stop');
    this.terminated = true;
    this.decoder.stop();
  }

  restart() {
    this.log('info', 'Restart');
    this.emit('restart');
    this.decoder.stop();
  }

  private play() {
    this.log('info', 'Play');
    this.fetchNowPlaying()
      .then(now => this.playNow(now))
      .catch(err => process.nextTick(() => this.emit('error', err)));
  }

  private playNow(now: INowPlaying) {
    this.log('info', 'Playing now "%s" from %d ms', now.title, now.offset);
    this.emit('title', now.title);
    this.decoder = decode(now.url, now.offset);
    this.decoder
      .on('end', this.playNextOrEndIfTerminated.bind(this))
      .on('error', err => process.nextTick(() => this.emit('error', err)))
      .pipe(wrap(this));
  }

  private fetchNowPlaying(): Promise<INowPlaying> {
    return this.backendService.getNowPlaying(this.channelId);
  }

  private playNextOrEndIfTerminated() {
    if (!this.terminated) {
      this.log('info', 'Going to play next');
      this.play();
    } else {
      this.log('info', 'Going to stop');
      this.end();
    }
  }

  toString(): string {
    return `stream(channelId=${this.channelId})`;
  }
}
