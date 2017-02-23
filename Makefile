IMAGE_ID := "docker push 205436866093.dkr.ecr.eu-central-1.amazonaws.com/myownradio:latest"
BASE_IMAGE_ID := "myownradio/backend-image"
CONTAINER_ID := "myownradio_service"

build:
	docker build -t $(IMAGE_ID) .

run:
	docker run --rm --env-file $(CURDIR)/.env --name $(CONTAINER_ID) -p 6060:6060 $(IMAGE_ID)

debug:
	docker run --rm -it --name $(CONTAINER_ID) -p 6060:6060 $(IMAGE_ID) bash

serve:
	docker run --rm --name $(CONTAINER_ID) -p 6060:6060 -v $(CURDIR):/var/app $(BASE_IMAGE_ID)

deploy:
	docker push $(IMAGE_ID)