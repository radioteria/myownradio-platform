import { IApiService } from '../api/apiService';
import { Multicast } from '../stream/helpers/multicast';
import { encode } from '../stream/ffmpeg/encode';
import { repeat } from '../stream/helpers/repeat';
import { decode } from '../stream/ffmpeg/decode';
import logger from './logger';

export class ChannelContainer {
  private channelStreamMap = new Map<string, Multicast>();

  constructor(private apiService: IApiService) {}

  public getMulticast(channelId: string): Multicast {
    if (!this.channelStreamMap.has(channelId)) {
      this.createChannelStream(channelId);
    }

    return this.channelStreamMap.get(channelId);
  }

  private createChannelStream(channelId: string) {
    const mc = new Multicast();

    const handleCreated = () => {};
    const handleGone = () => {};

    mc.on('created', handleCreated);
    mc.on('gone', handleGone);

    const channelStream = encode(
      repeat(async () => {
        const { url, offset, title } = await this.apiService.getNowPlaying(channelId);
        logger.info(`Now playing on ${channelId}: ${title} (${offset})`);
        return decode(url, offset);
      }),
    );

    channelStream.pipe(mc);

    this.channelStreamMap.set(channelId, mc);
  }
}
