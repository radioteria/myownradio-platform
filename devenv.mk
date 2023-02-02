UID := $(shell id -u)
GID := $(shell id -g)
PWD := $(shell pwd)
USER := $(UID):$(GID)
SERVICE_NAME := $(shell basename $(PWD))

.env:
	cp .env.example .env

devenv: prepare-devenv stop
	docker run --rm -it \
				--name $(SERVICE_NAME)-dev \
				--network myownradio-dev \
				--network-alias $(SERVICE_NAME) \
				$(foreach PORT_BINDING,$(PORT_BINDINGS),-p $(PORT_BINDING)) \
				--user $(USER) \
				-v "$(PWD)/.cargo-cache/git":/rust/.cargo/git \
				-v "$(PWD)/.cargo-cache/registry":/rust/.cargo/registry \
				-v "$(PWD)":/code \
				--env-file "$(PWD)/.env" \
				--env "HOME=/tmp" \
				--workdir=/code \
				$(SERVICE_NAME)-dev bash

stop:
	USER=$(USER) docker-compose stop $(SERVICE_NAME) || exit 0

rebuild:
	USER=$(USER) docker-compose build $(SERVICE_NAME)

start:
	USER=$(USER) docker-compose up $(SERVICE_NAME) -d

.PHONY: prepare-devenv, devenv, start, stop, rebuild
