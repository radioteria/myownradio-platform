IMAGE_ID := "myownradio/backend-core"
CONTAINER_ID := "mor-backend-service"

build:
	time docker build -t $(IMAGE_ID) .

run:
	docker run --rm --env-file $(CURDIR)/.env --name $(CONTAINER_ID) -p 6060:6060 $(IMAGE_ID)

debug:
	docker run --rm -it --name $(CONTAINER_ID) $(IMAGE_ID) bash

serve:
	docker run --rm --name $(CONTAINER_ID) -p 6060:6060 -v $(CURDIR):/var/app $(BASE_IMAGE_ID)

deploy:
	docker push $(IMAGE_ID)

.PHONY: build
