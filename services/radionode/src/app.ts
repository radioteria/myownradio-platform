import Application = require('koa');
import Router = require('koa-router');
import { EventEmitter } from 'events';

import { MorApiService } from './api/apiService';
import { ChannelContainer } from './services/channelContainer';
import logger from './services/logger';

export default function createApp() {
  const app = new Application();
  const router = new Router();

  const restartEmitter = new EventEmitter();
  const apiService = new MorApiService();
  const channelContainer = new ChannelContainer(apiService, restartEmitter);

  router.get('/', async ctx => {
    ctx.status = 200;
  });

  router.get('/listen/:channelId', async ctx => {
    const { channelId } = ctx.params;

    ctx.set('Content-Type', 'audio/mpeg');

    ctx.body = channelContainer.getMulticast(channelId).createStream();
  });

  router.post('/restart/:channelId', async ctx => {
    const { channelId } = ctx.params;

    logger.verbose(
      `Restart emitter contains ${restartEmitter.listenerCount('restart')} listener(s)`,
    );

    restartEmitter.emit('restart', channelId);

    ctx.body = 'OK';
  });

  app.use(router.routes());
  app.use(router.allowedMethods());

  return app;
}
