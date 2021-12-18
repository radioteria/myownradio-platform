USER := $(shell id -u):$(shell id -g)
PWD := $(shell pwd)

compose:
	mkdir -p services/backend/.cache/storage
	UID=$(shell id -u) GID=$(shell id -g) docker-compose up -d

shell:
	docker-compose exec backend-php-fpm sh

start-dev-dependencies:
	docker-compose up -d

stop-dev-dependencies:
	docker-compose stop

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
