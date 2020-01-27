IMAGE_ID := myownradio/backend
IMAGE_TAG := latest

build:
	docker build -t $(IMAGE_ID):$(IMAGE_TAG) .

push:
	docker push $(IMAGE_ID):$(IMAGE_TAG)

start:
	docker run -it --rm --env-file ./.env -p 8080:8080 $(IMAGE_ID):$(IMAGE_TAG)
