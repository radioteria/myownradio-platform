IMAGE_ID := "myownradio/backend-core"
CONTAINER_ID := "mor-backend-service"
DB_CONTAINER_ID := "mor-dev-db"
GIT_CURRENT_COMMIT := $(shell git rev-parse --verify HEAD)

develop:
	docker-compose up

build:
	time docker build -t $(IMAGE_ID) --build-arg GIT_CURRENT_COMMIT=$(GIT_CURRENT_COMMIT) .

deploy:
	git diff-index --quiet HEAD -- && docker push $(IMAGE_ID) || (echo 'You have uncommited changes.' && exit 1)

.PHONY: build
