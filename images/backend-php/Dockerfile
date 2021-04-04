FROM myownradio/backend-php-base:1.1.0

USER 0:0

RUN curl -sS https://getcomposer.org/download/1.10.21/composer.phar --output /usr/local/bin/composer && \
    chmod +x /usr/local/bin/composer

WORKDIR /code

COPY composer.json composer.lock ./
RUN composer install --no-plugins --no-scripts --no-dev

COPY . ./

FROM myownradio/backend-php-base:1.0.0

COPY --from=0 /code /code
