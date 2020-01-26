import { EventEmitter } from 'events';
import { IApiService } from '../api/apiService';
import { Multicast } from '../stream/helpers/multicast';
import { encode } from '../stream/ffmpeg/encode';
import { repeat } from '../stream/helpers/repeat';
import { decode } from '../stream/ffmpeg/decode';
import logger from './logger';
import { restartable } from '../stream/helpers/restartable';

const UNUSED_CHANNEL_CHECK_INTERVAL = 30000;

export class ChannelContainer {
  private channelStreamMap = new Map<string, Multicast>();

  constructor(private apiService: IApiService, private restartEmitter: EventEmitter) {
    this.watchUnusedChannels();
  }

  public getMulticast(channelId: string): Multicast {
    if (!this.channelStreamMap.has(channelId)) {
      this.createChannelStream(channelId);
    }

    return this.channelStreamMap.get(channelId);
  }

  private createChannelStream(channelId: string) {
    const mc = new Multicast();

    const radioStream = repeat(async () => {
      const {
        playlist_position,
        current_track: { url, offset, title },
      } = await this.apiService.getNowPlaying(channelId);
      const withJingle = (playlist_position - 1) % 3 === 0;
      logger.info(`Now playing on ${channelId}: ${title} (${offset})`);
      mc.metadataEmitter.changeTitle(title);
      return restartable(decode(url, offset, withJingle), channelId, this.restartEmitter);
    });

    const channelStream = encode(radioStream, true);

    channelStream.pipe(mc);

    mc.on('error', err => {
      channelStream.destroy(err);
      this.channelStreamMap.delete(channelId);
    });

    this.channelStreamMap.set(channelId, mc);
  }

  private watchUnusedChannels() {
    setInterval(() => this.checkUnusedChannels(), UNUSED_CHANNEL_CHECK_INTERVAL);
  }

  private checkUnusedChannels() {
    const now = new Date();
    this.channelStreamMap.forEach((multicast, channelId) => {
      if (
        multicast.clientsCount() === 0 &&
        now.getTime() - multicast.getUpdatedAt().getTime() > UNUSED_CHANNEL_CHECK_INTERVAL
      ) {
        logger.verbose(`Deleting unused channel ${channelId}`);
        multicast.destroy(new Error(`No listeners`));
      }
    });
  }
}
