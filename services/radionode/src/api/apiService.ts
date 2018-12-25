import axios from 'axios';

export interface INowPlaying {
  offset: number;
  title: string;
  url: string;
}

export interface IApiService {
  getNowPlaying(channelId: string): Promise<INowPlaying>;
}

export class MorApiService implements IApiService {
  private client = axios.create();

  public async getNowPlaying(channelId: string): Promise<INowPlaying> {
    const nowResponse = await this.client.get(
      `https://myownradio.biz/api/v0/stream/${channelId}/now`,
    );
    return nowResponse.data.data;
  }
}
