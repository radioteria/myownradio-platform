IMAGE_ID := myownradio/backend
IMAGE_TAG := latest

develop:
	@docker-compose up

build:
	docker build -t $(IMAGE_ID):$(IMAGE_TAG) .

deploy:
	@docker push $(IMAGE_ID):$(IMAGE_TAG)

start:
	docker run -it --rm --env-file ./.env -p 8080:8080 $(IMAGE_ID):$(IMAGE_TAG)
