IMAGE_ID := "myownradio/backend-core"
CONTAINER_ID := "mor-backend-service"
DB_CONTAINER_ID := "mor-dev-db"

build:
	time docker build -t $(IMAGE_ID) .

run:
	docker run --rm --env-file "$(CURDIR)/.env" --name $(CONTAINER_ID) -p 6060:6060 $(IMAGE_ID)

debug:
	docker run --rm -it --name $(CONTAINER_ID) $(IMAGE_ID) bash

serve:
	docker run --rm -it --name $(CONTAINER_ID) -p 6060:6060 -v "$(CURDIR)":/usr/app/ $(IMAGE_ID)

deploy:
	docker push $(IMAGE_ID)

start-dev-db:
	docker run --name $(DB_CONTAINER_ID) -e MYSQL_ROOT_PASSWORD=SuXXG00D -d -p 3306:3306 --restart=always mysql:5.6

.PHONY: build
