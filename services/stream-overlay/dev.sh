#!/usr/bin/env bash

docker-compose stop stream-overlay

docker run --rm -it \
  --network myownradio-dev \
  --user "$(id -u)":"$(id -g)" \
  -e HOME=/tmp \
  --workdir /code \
  -v "$(pwd)":/code \
  -l 'traefik.enable=true' \
  -l 'traefik.http.routers.overlay.rule=PathPrefix(`/stream-overlay`)' \
  -l 'traefik.http.services.overlay.loadbalancer.server.port=3000' \
  node:18 bash
