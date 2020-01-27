IMAGE_TAG := "latest"
IMAGE_NAME := "myownradio/radionode"

build:
	docker build -t $(IMAGE_NAME):$(IMAGE_TAG) .

push:
	docker push $(IMAGE_NAME):$(IMAGE_TAG)

start:
	docker run -it --rm -p 8080:8080 $(IMAGE_NAME):$(IMAGE_TAG)

compose-ps:
	docker-compose exec radionode watch ps ax
