IMAGE_ID := "myownradio/backend-core"
GIT_CURRENT_COMMIT := $(shell git rev-parse --verify HEAD)

develop:
	@docker-compose up

build:
	@time docker build -t $(IMAGE_ID):${TAG} --build-arg GIT_CURRENT_COMMIT=$(GIT_CURRENT_COMMIT) .

deploy:
	@docker push $(IMAGE_ID):${TAG}

last-tag:
	@git describe --abbrev=0 --tags

.PHONY: build
