include ../../devenv.mk

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
