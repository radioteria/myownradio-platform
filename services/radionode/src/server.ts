import Application = require('koa');
import Router = require('koa-router');

import { MorApiService } from './api/apiService';
import { ChannelContainer } from './services/channelContainer';

const app = new Application();
const port = process.env.PORT || 8080;
const router = new Router();

const apiService = new MorApiService();
const channelContainer = new ChannelContainer(apiService);

router.get('/stream/:channelId', async (ctx: Application.Context) => {
  const { channelId } = ctx.params;

  ctx.set('Content-Type', 'audio/mpeg');

  ctx.body = channelContainer.getMulticast(channelId).create();
});

app.use(router.routes());
app.use(router.allowedMethods());

app.listen(port, () => {
  console.log(`Server is listening on port ${port}`);
});
