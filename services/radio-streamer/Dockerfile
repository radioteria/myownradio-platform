FROM ubuntu:20.04

WORKDIR /code

RUN \
    apt-get update && \
    DEBIAN_FRONTEND=noninteractive apt-get install -y --no-install-recommends \
        curl \
        gcc && \
    curl --proto '=https' --tlsv1.2 -sSf https://sh.rustup.rs | sh -s -- -y --default-toolchain nightly

COPY Cargo.lock /code/Cargo.lock
COPY Cargo.toml /code/Cargo.toml

# Hack to make Cargo download and cache dependencies
RUN \
    mkdir src && \
    echo "fn main() {}" > src/main.rs && \
    /root/.cargo/bin/cargo +nightly build --release && \
    rm -rf src

COPY src /code/src

RUN \
    # TODO: Next line is a workaround for https://github.com/rust-lang/cargo/issues/7969
    touch src/main.rs && \
    /root/.cargo/bin/cargo +nightly test --release && \
    /root/.cargo/bin/cargo +nightly build --release && \
    mv target/release/radio-streamer radio-streamer && \
    rm -rf target

FROM ubuntu:20.04

USER nobody:nogroup

COPY --from=0 /code/radio-streamer /radio-streamer

CMD ["/radio-streamer"]
