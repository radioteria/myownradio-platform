USER := $(shell id -u):$(shell id -g)
PWD := $(shell pwd)

start-dev-dependencies:
	docker-compose up -d

stop-dev-dependencies:
	docker-compose stop

#enter-dev-environment:
#	docker build -t musicloud-dev --build-arg USER=$(USER) -f Dockerfile .
#	mkdir -p .cache/volume/temp
#	mkdir -p .cache/volume/media
#	mkdir -p .cache/home
#	docker run --rm -it --name musicloud-dev \
#			--network musicloud \
#			-p 127.0.0.1:8080:8080 \
#			-v "$(PWD)":/code \
#			-v "$(PWD)/.cache/volume/temp":/volume/temp \
#			-v "$(PWD)/.cache/volume/media":/volume/media \
#			-v "$(PWD)/.cache/home":/home \
#			musicloud-dev bash

run-database-migration:
	docker build -t myownradio-migration -f images/migration/Dockerfile .
	docker run --rm --name myownradio-migration \
			--network myownradio-dev \
			--env MYSQL_HOST=db \
			--env MYSQL_DB=mor \
			--env MYSQL_USER=mor \
			--env MYSQL_PASSWORD=mor \
			myownradio-migration

build-backend-images:
	docker build -t pldin601/myownradio-backend-nginx:latest --file images/backend-nginx/Dockerfile services/backend/
	docker build -t pldin601/myownradio-backend-php:latest --file images/backend-php/Dockerfile services/backend/

push-backend-images:
	docker push pldin601/myownradio-backend-nginx:latest
	docker push pldin601/myownradio-backend-php:latest
