FROM myownradio/backend-image

MAINTAINER Roman Lakhtadyr <roman.lakhtadyr@gmail.com>

USER mor

WORKDIR /var/app/public/

COPY . .

RUN	mkdir -p storage/logs && \
	mkdir -p storage/sessions && \
	mkdir -p storage/images/avatars && \
	mkdir -p storage/images/covers && \
	composer install && \
	bower install
