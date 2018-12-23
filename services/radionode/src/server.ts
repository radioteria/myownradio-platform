import Application = require('koa');
import Router = require('koa-router');
import { Subject } from 'rxjs';
import { tap } from 'rxjs/operators';

import Container from './app/core/container';
import { MorBackendService } from './app/service/backend/impl/mor';
import { PassThrough } from 'stream';
import decodeMedia from './media/decodeMedia';
import writeToStream from './media/writeToStream';
import { createEncoder } from './app/core/encoder';

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

  const pauseSubject = new Subject<boolean>();
  const passThrough = new PassThrough();

  const nowPlaying = await morBackend.getNowPlaying(channelId);

  console.log('start stream');

  decodeMedia(nowPlaying.url, nowPlaying.offset, pauseSubject)
    .pipe(writeToStream(passThrough, pauseSubject))
    .subscribe({
      complete: () => {
        console.log('done stream');
      },
    });

  ctx.body = passThrough.pipe(createEncoder());
});

app.use(router.routes());
app.use(router.allowedMethods());

app.listen(port, () => {
  console.log(`Server is listening on port ${port}`);
});
