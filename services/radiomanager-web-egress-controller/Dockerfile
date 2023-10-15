FROM rust:1.73 as devenv

ARG USER=1000:1000

WORKDIR /code/


FROM rust:1.73 as build

WORKDIR /code/

COPY Cargo.toml ./
COPY Cargo.lock ./
COPY src ./src

RUN cargo build --release

FROM rust:1.73

COPY --from=build /code/target/release/radiomanager-web-egress-controller /radiomanager-web-egress-controller

CMD /radiomanager-web-egress-controller
