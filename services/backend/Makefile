IMAGE_ID := "myownradio/myownradio-universe"
GIT_CURRENT_COMMIT := $(shell git rev-parse --verify HEAD)
TAG := "latest"

develop:
	@docker-compose up

build:
	@time docker build -t $(IMAGE_ID):${TAG} --build-arg GIT_CURRENT_COMMIT=$(GIT_CURRENT_COMMIT) .

deploy:
	@docker push $(IMAGE_ID):${TAG}

last-tag:
	@git describe --abbrev=0 --tags

.PHONY: build

