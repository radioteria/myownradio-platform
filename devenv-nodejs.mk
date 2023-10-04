UID := $(shell id -u)
GID := $(shell id -g)
PWD := $(shell pwd)
USER := $(UID):$(GID)
SERVICE_NAME := $(shell basename $(PWD))

prepare-devenv: .env
	docker build -t $(SERVICE_NAME)-dev --build-arg USER=$(USER) -f Dockerfile --target devenv .
