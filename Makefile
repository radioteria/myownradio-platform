IMAGE_ID := "myownradio/backend-core"
CONTAINER_ID := "mor-backend-service"
DB_CONTAINER_ID := "mor-dev-db"
GIT_CURRENT_COMMIT := $(shell git rev-parse --verify HEAD)

develop:
	docker-compose up

build:
	time docker build -t $(IMAGE_ID):${TAG} --build-arg GIT_CURRENT_COMMIT=$(GIT_CURRENT_COMMIT) .

deploy:
	docker push $(IMAGE_ID):${TAG}

.PHONY: build
