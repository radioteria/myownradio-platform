import Application = require('koa');
import Router = require('koa-router');

import Container from './app/core/container';
import { MorBackendService } from './app/service/backend/impl/mor';
import { PassThrough } from 'stream';
import { createDecoder } from './stream/createDecoder';
import { createEncoder } from './stream/createEncoder';
import { createRepeatable } from './stream/createRepeatable';

const morBackend = new MorBackendService();
const container = new Container(morBackend);

const app = new Application();
const port = process.env.PORT || 8080;

const router = new Router();

router.get('/audio/:channelId', (ctx: Application.Context) => {
  const { channelId } = ctx.params;
  const player = container.createOrGetPlayer(channelId);

  ctx.set({ 'Content-Type': 'audio/mpeg' });
  const pt = new PassThrough();

  ctx.body = pt;

  player.addClient(pt);
});

router.get('/stream/:channelId', async (ctx: Application.Context) => {
  const { channelId } = ctx.params;

  const stream = createRepeatable(async () => {
    const { url, offset, title } = await morBackend.getNowPlaying(channelId);
    console.log(`Now playing: ${title} (${offset})`);
    return createDecoder(url, offset);
  });

  ctx.body = createEncoder(stream);
});

app.use(router.routes());
app.use(router.allowedMethods());

app.listen(port, () => {
  console.log(`Server is listening on port ${port}`);
});
