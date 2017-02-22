FROM myownradio/backend-image

MAINTAINER Roman Lakhtadyr <roman.lakhtadyr@gmail.com>

COPY * /var/app/

WORKDIR /var/app/

RUN composer install && \
    npm install -g bower gulp