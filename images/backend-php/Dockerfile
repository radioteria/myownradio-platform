# TODO bump the base image version in ALL stages
FROM myownradio/backend-php-base:3.0.6

USER 0:0

WORKDIR /code

COPY composer.json composer.lock ./
RUN composer install --no-plugins --no-scripts --no-dev

COPY . ./

ENV DUMMY=1

# TODO bump version here
FROM myownradio/backend-php-base:3.0.6

COPY --from=0 /code /code
