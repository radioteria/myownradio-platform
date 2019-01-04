import axios from 'axios';
import MockAdapter from 'axios-mock-adapter';
import { MorApiService } from './apiService';
import firstNowPlayingResponse from '../../tests/__fixtures/firstNowPlayingResponse';

const mock = new MockAdapter(axios);

const morApiService = new MorApiService();

test('Should correctly get now playing info using API', async () => {
  mock.onGet('https://myownradio.biz/api/v0/stream/66/now').replyOnce(200, firstNowPlayingResponse);

  const nowPlaying = await morApiService.getNowPlaying('66');

  expect(nowPlaying).toEqual({
    offset: 104865,
    title: 'Track Artist - Track Title',
    url: 'https://s3.eu-central-1.amazonaws.com/myownradio-files/path-to-track.mp3',
  });
});
