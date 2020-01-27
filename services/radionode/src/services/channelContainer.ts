import { EventEmitter } from 'events';
import axios from 'axios';
import logger from './logger';
import { IApiService } from '../api/apiService';
import { Multicast } from '../stream/helpers/multicast';
import { encode } from '../stream/ffmpeg/encode';
import { repeat } from '../stream/helpers/repeat';
import { decode } from '../stream/ffmpeg/decode';
import { restartable } from '../stream/helpers/restartable';
import toNull from '../stream/helpers/toNull';

const UNUSED_CHANNEL_CHECK_INTERVAL = 30000;

export class ChannelContainer {
  private client = axios.create();
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
        playlist_position: playlistPosition,
        current_track: currentTrack,
        next_track: nextTrack,
      } = await this.apiService.getNowPlaying(channelId);

      mc.metadataEmitter.changeTitle(currentTrack.title);

      logger.info(`Now playing on ${channelId}: ${currentTrack.title} (${currentTrack.offset})`);

      const shouldBeJingle = currentTrack.offset < 1000 && (playlistPosition - 1) % 4 === 0;

      this.triggerNextTrackPreload(nextTrack.url);

      return restartable(
        decode(currentTrack.url, currentTrack.offset, shouldBeJingle),
        channelId,
        this.restartEmitter,
      );
    });

    const channelStream = encode(radioStream, true);

    channelStream.pipe(mc);

    mc.on('error', err => {
      channelStream.destroy(err);
      this.channelStreamMap.delete(channelId);
    });

    this.channelStreamMap.set(channelId, mc);
  }

  private triggerNextTrackPreload(url: string) {
    this.client
      .get(url, {
        responseType: 'stream',
      })
      .then(
        ({ data }) => {
          data.pipe(toNull()).once('finish', () => {
            logger.verbose('Next track preload finished');
          });
        },
        () => {},
      );
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
