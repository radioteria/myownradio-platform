FROM rust:1.66.0 as base

ENV DEBIAN_FRONTEND=noninteractive

ARG FFMPEG_VERSION=5.1
ARG FFMPEG_URL="https://ffmpeg.org/releases/ffmpeg-$FFMPEG_VERSION.tar.bz2"

RUN apt-get update

RUN apt-get install -y --no-install-recommends nasm yasm libclang-dev libssl-dev

WORKDIR /build

RUN \
      wget -O ffmpeg.tar.bz2 "$FFMPEG_URL" && \
      tar xf ffmpeg.tar.bz2 && \
      cd ffmpeg-* && \
      ./configure \
      --disable-everything \
      --enable-shared \
      --enable-avutil \
      --enable-avcodec \
      --enable-avformat \
      --enable-avdevice \
      --enable-avfilter \
      --enable-openssl \
      --enable-swresample \
      --enable-openssl \
      --enable-decoder=flac,pcm_s16le,aac,mp3,vorbis \
      --enable-demuxer=flac,wav,aac,mov,mp4,m4a,3gp,3g2,mj2,mp3,ogg \
      --enable-protocol=file,pipe,http,https,tls \
      && make install \
      && ldconfig


FROM base as build

WORKDIR /code

COPY Cargo.lock /code/Cargo.lock
COPY Cargo.toml /code/Cargo.toml
COPY tests /code/tests

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
    # cargo test --release && \
    cargo build --release && \
    mv target/release/radio-streamer radio-streamer && \
    rm -rf target

FROM base as devenv

ARG USER=1000:1000

RUN mkdir /rust && chown $USER /rust

USER $USER

ENV HOME=/rust

WORKDIR /code


FROM base

USER nobody:nogroup

COPY --from=build /code/radio-streamer /radio-streamer

CMD ["/radio-streamer"]
