UID := $(shell id -u)
GID := $(shell id -g)
PWD := $(shell pwd)
USER := $(UID):$(GID)
SERVICE_NAME := $(shell basename $(PWD))

prepare-devenv: .env
	mkdir -p .cargo-cache/git
	mkdir -p .cargo-cache/registry
	mkdir -p target
	docker build -t $(SERVICE_NAME)-dev --build-arg USER=$(USER) -f Dockerfile --target devenv .
