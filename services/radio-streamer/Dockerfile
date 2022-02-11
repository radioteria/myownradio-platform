FROM rust

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

USER nobody:nogroup

COPY --from=0 /code/radio-streamer /radio-streamer

COPY --from=pldin601/static-ffmpeg:22.02.112002-11fbed4 /ffmpeg /usr/local/bin/
COPY --from=pldin601/static-ffmpeg:22.02.112002-11fbed4 /ffprobe /usr/local/bin/

CMD ["/radio-streamer"]
