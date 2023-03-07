FROM rust:1.66.0

ENV DEBIAN_FRONTEND=noninteractive

RUN apt-get update

RUN apt-get install -y --no-install-recommends \
    libavutil-dev libavformat-dev libavfilter-dev libavdevice-dev libclang-dev

WORKDIR /code

COPY Cargo.lock /code/Cargo.lock
COPY Cargo.toml /code/Cargo.toml

# Hack to make Cargo download and cache dependencies
RUN \
    mkdir src && \
    echo "fn main() {}" > src/main.rs && \
    cargo build --release && \
    rm -rf src

COPY src /code/src

RUN \
    # TODO: Next line is a workaround for https://github.com/rust-lang/cargo/issues/7969
    touch src/main.rs && \
    cargo test --release && \
    cargo build --release && \
    mv target/release/radio-streamer radio-streamer && \
    rm -rf target

FROM ubuntu:20.04

RUN DEBIAN_FRONTEND=noninteractive apt-get update && apt-get install -y --no-install-recommends \
    libavutil-dev libavformat-dev libavfilter-dev libavdevice-dev

USER nobody:nogroup

COPY --from=0 /code/radio-streamer /radio-streamer

COPY --from=mwader/static-ffmpeg:5.0.1-3 /ffmpeg /usr/local/bin/
COPY --from=mwader/static-ffmpeg:5.0.1-3 /ffprobe /usr/local/bin/

CMD ["/radio-streamer"]
