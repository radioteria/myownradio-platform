import Application = require('koa');
import Router = require('koa-router');

import { decode } from './stream/ffmpeg/decode';
import { encode } from './stream/ffmpeg/encode';
import { repeat } from './stream/helpers/repeat';
import { MorApiService } from './api/apiService';

const apiService = new MorApiService();
const app = new Application();
const port = process.env.PORT || 8080;
const router = new Router();

router.get('/stream/:channelId', async (ctx: Application.Context) => {
  const { channelId } = ctx.params;

  const stream = encode(
    repeat(async () => {
      const { url, offset, title } = await apiService.getNowPlaying(channelId);
      console.log(`Now playing: ${title} (${offset})`);
      return decode(url, offset);
    }),
  );

  ctx.body = stream;
});

app.use(router.routes());
app.use(router.allowedMethods());

app.listen(port, () => {
  console.log(`Server is listening on port ${port}`);
});
