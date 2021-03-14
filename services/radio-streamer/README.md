# radio-streamer

### Development environment
In order to fill necessary variables `exports` file can be created like this:
```bash
export LOG_LEVEL=Debug
export MOR_BACKEND_URL=https://myownradio.biz
export STREAM_MUTATION_TOKEN=secret
```

Then install `ffmpeg` package into your system. For Ubuntu, it would be like this:
```bash
sudo apt install ffmpeg
```

Now, start the server:
```bash
cargo run
```
