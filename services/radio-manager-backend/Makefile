UID := $(shell id -u)
GID := $(shell id -g)
PWD := $(shell pwd)
USER := $(UID):$(GID)
SERVICE_NAME := $(shell basename $(PWD))

prepare-devenv:
	mkdir -p .cargo-cache/git
	mkdir -p .cargo-cache/registry
	mkdir -p target
	docker build -t $(SERVICE_NAME)-dev --build-arg USER=$(USER) -f Dockerfile-dev .

stop:
	USER=$(USER) docker-compose stop $(SERVICE_NAME)

start:
	USER=$(USER) docker-compose build $(SERVICE_NAME)
	USER=$(USER) docker-compose up $(SERVICE_NAME) -d

devenv: prepare-devenv stop
	docker run --rm -it \
				--name $(SERVICE_NAME)-dev \
				--network myownradio-dev \
				--network-alias $(SERVICE_NAME) \
				-p 127.0.0.1:40005:8080 \
				--user $(USER) \
				-v "$(PWD)/.cargo-cache/git":/rust/.cargo/git \
				-v "$(PWD)/.cargo-cache/registry":/rust/.cargo/registry \
				-v "$(PWD)":/code \
				$(SERVICE_NAME)-dev bash

.PHONY: prepare, devenv
