UID := $(shell id -u)
GID := $(shell id -g)
PWD := $(shell pwd)
USER := $(UID):$(GID)

prepare-devenv:
	mkdir -p .cargo-cache/git
	mkdir -p .cargo-cache/registry
	mkdir -p target
	docker build -t radio-manager-backend-dev --build-arg USER=$(USER) -f Dockerfile-dev .

stop:
	USER=$(USER) docker-compose stop radio-manager-backend

start:
	USER=$(USER) docker-compose build radio-manager-backend
	USER=$(USER) docker-compose up radio-manager-backend -d

devenv: prepare-devenv stop
	docker run --rm -it \
				--name radio-manager-backend-dev \
				--network myownradio-dev \
				--network-alias radio-manager-backend \
				-p 127.0.0.1:40005:8080 \
				--user $(USER) \
				-v "$(PWD)/.cargo-cache/git":/rust/.cargo/git \
				-v "$(PWD)/.cargo-cache/registry":/rust/.cargo/registry \
				-v "$(PWD)":/code \
				radio-manager-backend-dev bash

.PHONY: prepare, devenv
