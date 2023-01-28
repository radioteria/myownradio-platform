include ../../devenv.mk

prepare-devenv: .env
	# build php image
	docker build -t $(SERVICE_NAME)-dev -f Dockerfile --target php-devenv .

	# install php compose
	docker run --rm --user=$(USER) \
					-v "$(PWD)":/code \
					--workdir=/code \
					$(SERVICE_NAME)-dev /bin/sh -c "HOME=/tmp composer install"
