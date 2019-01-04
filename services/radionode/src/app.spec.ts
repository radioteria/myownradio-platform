import * as agent from 'supertest-koa-agent';
import createApp from './app';

const app = agent(createApp());

test('Should get 200 on request to /', async () => {
  const response = await app.get('/');
  expect(response.status).toBe(200);
});
