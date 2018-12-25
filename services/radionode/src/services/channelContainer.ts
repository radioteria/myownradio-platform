import { IApiService } from '../api/apiService';
import { Multicast } from '../stream/helpers/multicast';
import { encode } from '../stream/ffmpeg/encode';
import { repeat } from '../stream/helpers/repeat';
import { decode } from '../stream/ffmpeg/decode';
import logger from './logger';

export class ChannelContainer {
  private channelStreamMap = new Map<string, Multicast>();

  constructor(private apiService: IApiService) {
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

    const channelStream = encode(
      repeat(async () => {
        const { url, offset, title } = await this.apiService.getNowPlaying(channelId);
        logger.info(`Now playing on ${channelId}: ${title} (${offset})`);
        return decode(url, offset);
      }),
      true,
    );

    channelStream.pipe(mc);

    mc.on('error', err => channelStream.destroy(err));

    this.channelStreamMap.set(channelId, mc);
  }

  private watchUnusedChannels() {
    setInterval(() => this.checkUnusedChannels(), 30000);
  }

  private checkUnusedChannels() {
    logger.verbose(`Checking for unused channels...`);
    const now = new Date();
    this.channelStreamMap.forEach((multicast, channelId) => {
      if (multicast.count() === 0 && now.getTime() - multicast.getUpdatedAt().getTime() > 30000) {
        logger.verbose(`Deleting unised channel ${channelId}`);
        this.channelStreamMap.delete(channelId);
        multicast.destroy(new Error(`No listeners`));
      }
    });
  }
}
