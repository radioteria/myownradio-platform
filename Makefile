USER := $(shell id -u):$(shell id -g)
PWD := $(shell pwd)

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
