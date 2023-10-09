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

## Use the official Rust base image
FROM rust:1.73

# Install GStreamer and its dependencies
RUN apt-get update && apt-get install -y \
    xvfb \
    gstreamer1.0-plugins-base \
    gstreamer1.0-plugins-good \
    gstreamer1.0-plugins-bad \
    gstreamer1.0-plugins-ugly \
    gstreamer1.0-libav \
    gstreamer1.0 \
    libgirepository1.0-dev

WORKDIR /code/

# Copy compiled GStreamer plugins
COPY --from=gst-plugins /src/gst-plugins-rs/target/* /opt/gst-plugins-rs/
COPY --from=gst-plugins /src/gstcefsrc/build/* /opt/gstcefsrc/

ENV GST_PLUGIN_PATH="/opt/gst-plugins-rs/debug:/opt/gstcefsrc:$GST_PLUGIN_PATH"

# Check whether GStreamer was successfully installed
RUN gst-inspect-1.0 webrtcsink
RUN xvfb-run -a --server-args="-nolisten tcp -screen 0 1280x720x30+32" gst-inspect-1.0 cefsrc
