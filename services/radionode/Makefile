IMAGE_NAME := "myownradio/radionode"

docker-build:
	docker build -t $(IMAGE_NAME) .

compose-ps:
	docker-compose exec radionode watch ps ax
