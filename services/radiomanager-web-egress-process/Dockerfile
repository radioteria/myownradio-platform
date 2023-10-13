FROM ubuntu:22.04 as gst-plugins

ENV DEBIAN_FRONTEND=noninteractive

# Install build dependencies
RUN apt-get update && \
    apt-get install -y \
      cmake \
      xvfb \
      git \
      pkg-config \
      curl \
      build-essential \
      libssl-dev \
      libglib2.0-dev \
      gstreamer1.0 \
      libgstreamer-plugins-base1.0-dev \
      libgstreamer-plugins-bad1.0-dev

# Install Rust, Cargo and Cargo C-ABI helpers
RUN curl --proto '=https' --tlsv1.2 -sSf https://sh.rustup.rs | sh -s -- -y
ENV PATH="/root/.cargo/bin:${PATH}"
RUN cargo install cargo-c

WORKDIR /src

# Build gst-plugins-rs plugins
RUN git clone https://gitlab.freedesktop.org/gstreamer/gst-plugins-rs.git && \
    cd gst-plugins-rs && \
    cargo cbuild -p gst-plugin-webrtc

# Check whether webrtc plugins were successfully installed
RUN GST_PLUGIN_PATH="$(echo -n gst-plugins-rs/target/*-unknown-linux-gnu/debug):$GST_PLUGIN_PATH" gst-inspect-1.0 webrtcsink

# Build gstcefsrc plugin
RUN git clone https://github.com/centricular/gstcefsrc.git && \
    cd gstcefsrc && \
    mkdir build && cd build && \
    cmake -G "Unix Makefiles" -DCMAKE_BUILD_TYPE=Release .. && \
    make

# Check whether gstcefsrc plugin was successfully installed
RUN GST_PLUGIN_PATH="gstcefsrc/build/Release:$GST_PLUGIN_PATH" xvfb-run -a --server-args="-nolisten tcp -screen 0 1280x720x30+32" gst-inspect-1.0 cefsrc

FROM ubuntu:22.04 as build-deps


ENV DEBIAN_FRONTEND=noninteractive

# Install GStreamer and dependencies
RUN apt-get update && \
    apt-get install -y \
    curl \
    xvfb \
    gstreamer1.0-plugins-base \
    gstreamer1.0-plugins-good \
    gstreamer1.0-plugins-bad \
    gstreamer1.0-plugins-ugly \
    gstreamer1.0-libav \
    gstreamer1.0 \
    libgirepository1.0-dev

# Copy compiled GStreamer plugins
COPY --from=gst-plugins /src/gst-plugins-rs/target/* /opt/gst-plugins-rs/
COPY --from=gst-plugins /src/gstcefsrc/build/* /opt/gstcefsrc/

ENV GST_PLUGIN_PATH="/opt/gst-plugins-rs/debug:/opt/gstcefsrc:$GST_PLUGIN_PATH"


FROM build-deps as devenv

ARG USER=1000:1000

# Install Rust and Cargo helpers
ENV RUSTUP_HOME=/opt/rust
ENV CARGO_HOME=/opt/rust

RUN mkdir /opt/rust && chown $USER /opt/rust
RUN curl --proto '=https' --tlsv1.2 -sSf https://sh.rustup.rs | sh -s -- -y --no-modify-path
ENV PATH="/opt/rust/bin:${PATH}"

WORKDIR /code/

# Check whether GStreamer was successfully installed
RUN gst-inspect-1.0 webrtcsink
RUN xvfb-run -a --server-args="-nolisten tcp -screen 0 1280x720x30+32" gst-inspect-1.0 cefsrc

FROM build-deps as build

RUN curl --proto '=https' --tlsv1.2 -sSf https://sh.rustup.rs | sh -s -- -y
ENV PATH="/root/.cargo/bin:${PATH}"

WORKDIR /code/

COPY Cargo.toml ./
COPY Cargo.lock ./
COPY src ./src

RUN ls -l && cargo build --release

FROM build-deps

COPY --from=build /code/target/release/radiomanager-web-egress-process /radiomanager-web-egress-process

CMD ["/radiomanager-web-egress-process"]
