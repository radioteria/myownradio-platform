import axios from 'axios';

import { BackendService, ClientSessionId, NowPlaying } from '../';

export class MorBackendService implements BackendService {
  private client = axios.create();

  get name(): string {
    return 'mor';
  }

  public async getNowPlaying(channelId: string): Promise<NowPlaying> {
    const nowResponse = await this.client.get(
      `http://myownradio.biz/api/v0/stream/${channelId}/now`,
    );
    return nowResponse.data.data;
  }

  public async createClientSession(channelId: string): Promise<ClientSessionId> {
    throw new Error('Method not implemented.');
  }

  public async deleteClientSession(clientSessionId: ClientSessionId): Promise<void> {
    throw new Error('Method not implemented.');
  }
}
