import Application = require('koa');
import Router = require('koa-router');
import { EventEmitter } from 'events';

import { MorApiService } from './api/apiService';
import { ChannelContainer } from './services/channelContainer';
import { withMetadataTransform } from './stream/metadata/icyMetadataTransform';
import logger from './services/logger';
import config from './config';

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

    const channelInfo = await apiService.getInfo(channelId);

    if (!channelInfo || channelInfo.status !== 1) {
      return;
    }

    const withIcyMetadata = ctx.get('icy-metadata') === '1';

    const multicast = channelContainer.getMulticast(channelId);
    const readable = multicast.createStream();

    ctx.set('Content-Type', 'audio/mpeg');

    if (withIcyMetadata) {
      ctx.set('icy-metadata', '1');
      ctx.set('icy-metaint', String(config.icyMetadataInterval));
      ctx.set('icy-name', channelInfo.name);

      ctx.body = withMetadataTransform(readable, multicast.metadataEmitter);
    } else {
      ctx.body = readable;
    }
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
