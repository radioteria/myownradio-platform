import axios from 'axios';

export interface INowPlaying {
  playlist_position;
  current_track: {
    offset: number;
    title: string;
    url: string;
  };
  next_track: {
    title: string;
    url: string;
  };
}

export interface IApiService {
  getNowPlaying(channelId: string): Promise<INowPlaying>;
}

export class MorApiService implements IApiService {
  private client = axios.create();

  public async getNowPlaying(channelId: string): Promise<INowPlaying> {
    interface IMyOwnRadioResponse {
      code: number;
      message: string;
      data: INowPlaying;
    }

    const nowResponse = await this.client.get<IMyOwnRadioResponse>(
      `https://myownradio.biz/api/v1/stream/${channelId}/now`,
    );

    return nowResponse.data.data;
  }
}
