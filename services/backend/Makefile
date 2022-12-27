include ../../devenv.mk

IMAGE_ID := myownradio/backend
IMAGE_TAG := latest

build:
	docker build -t $(IMAGE_ID):$(IMAGE_TAG) .

push:
	docker push $(IMAGE_ID):$(IMAGE_TAG)

start:
	docker run -it --rm --env-file ./.env -p 8080:8080 $(IMAGE_ID):$(IMAGE_TAG)


prepare-devenv: .env
	# build php image
	docker build -t $(SERVICE_NAME)-dev -f Dockerfile --target php-base .

	# build frontend
	docker run --rm --user=$(USER) \
					-v "$(PWD)":/code \
					--workdir=/code \
					node:12-alpine /bin/sh -c "HOME=/tmp && npm i && npm run bower:install && npm run build"

	# install php compose
	docker run --rm --user=$(USER) \
					-v "$(PWD)":/code \
					--workdir=/code \
					$(SERVICE_NAME)-dev /bin/sh -c "HOME=/tmp composer install"
