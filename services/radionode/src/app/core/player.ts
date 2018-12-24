import * as EventEmitter from 'events';
import { Context } from 'koa';

import { module } from '../utils/log-utils';
import { createEncoder } from './encoder';
import Stream from './stream';
import Broadcast from './broadcast';
import { IApiService } from '../service/backend';
import { Transform, Writable } from 'stream';
import { createTransformWithConnectors } from '../utils/stream-utils';

export default class Player extends EventEmitter {
  channelId: string;
  title: string;

  stream: Stream;
  broadcast: Broadcast;
  log = module(this);

  private startedAt = Date.now();

  constructor(backendService: IApiService, channelId: string) {
    super();

    this.channelId = channelId;
    this.stream = new Stream(backendService, channelId);

    this.broadcast = new Broadcast();

    this.bindEventHandlers();
    this.connectStreamToBroadcast();

    this.log('info', 'Initialized');
  }

  addClient(client: Writable) {
    this.broadcast.addClient(client);
  }

  countClients(): number {
    return this.broadcast.count();
  }

  countUptime(): number {
    return Date.now() - this.startedAt;
  }

  stop() {
    this.log('info', 'Stop');
    this.stream.stop();
    this.broadcast.clear();
  }

  private bindEventHandlers() {
    this.stream.on('error', (error: any) => {
      this.log('error', error);
    });
    this.stream.on('title', title => {
      this.title = title;
    });

    this.broadcast.on('gone', () => {
      this.log('info', 'Client is gone');
      this.emit('gone');
      if (this.broadcast.count() === 0) {
        this.log('info', 'Player is idle');
        this.emit('idle');
      }
    });
    this.broadcast.on('new', () => {
      this.log('info', 'New client');
      this.emit('new');
    });
  }

  private connectStreamToBroadcast() {
    this.log('info', 'Init encoder chain');
    this.stream.pipe(createEncoder()).pipe(this.broadcast);
  }

  toString(): string {
    return `player(channelId=${this.channelId})`;
  }
}
