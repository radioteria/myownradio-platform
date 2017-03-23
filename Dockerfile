FROM myownradio/backend-image

MAINTAINER Roman Lakhtadyr <roman.lakhtadyr@gmail.com>

COPY . /var/app

WORKDIR /var/app

RUN apt-get install -y mc && \
    composer install && \
    npm install && \

    npm -- run bower --allow-root install && \
    npm run gulp scripts && \

    mkdir -p storage/cache && \

    chown --recursive mor:mor .

VOLUME /var/app/storage
