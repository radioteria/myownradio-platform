import Application = require('koa');
import Router = require('koa-router');

import Container from './app/core/container';
import { MorBackendService } from './app/service/backend/impl/mor';
import { PassThrough } from 'stream';
import { createDecoder } from './stream/createDecoder';
import { createEncoder } from './stream/createEncoder';

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

  const { url, offset } = await morBackend.getNowPlaying(channelId);
  const stream = createDecoder(url, offset);
  const mpeg = createEncoder(stream);

  ctx.body = mpeg;
});

app.use(router.routes());
app.use(router.allowedMethods());

app.listen(port, () => {
  console.log(`Server is listening on port ${port}`);
});
