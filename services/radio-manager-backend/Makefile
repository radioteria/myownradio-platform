USER := $(shell id -u):$(shell id -g)
PWD := $(shell pwd)

enter-dev-environment:
	mkdir -p .cargo-cache/git
	mkdir -p .cargo-cache/registry
	mkdir -p target
	docker build -t radio-manager-dev --build-arg USER=$(USER) -f Dockerfile-dev .
	docker run --rm -it --name radio-streamer-dev \
			-p 127.0.0.1:8080:8080 \
			-v "$(PWD)/.cargo-cache/git":/rust/.cargo/git \
			-v "$(PWD)/.cargo-cache/registry":/rust/.cargo/registry \
			-v "$(PWD)":/code \
			radio-manager-dev bash
