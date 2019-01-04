import createApp from './app';

const port = process.env.PORT || 8080;
const app = createApp();

app.listen(port, () => {
  console.log(`Server is listening on port ${port}`);
});
