FROM php:7.4-fpm-alpine

MAINTAINER Roman Lakhtadyr <roman.lakhtadyr@gmail.com>

ARG MAX_UPLOAD_FILESIZE=256
ENV MAX_UPLOAD_FILESIZE=${MAX_UPLOAD_FILESIZE}

RUN apk add curl-dev libpng-dev oniguruma-dev mediainfo

RUN docker-php-ext-install curl fileinfo json iconv gd pdo pdo_mysql mbstring

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" && \
    rm "$PHP_INI_DIR/php.ini-development" && \
    sed -i "s/^upload_max_filesize\s=.*/upload_max_filesize = ${MAX_UPLOAD_FILESIZE}M/" $PHP_INI_DIR/php.ini && \
    sed -i "s/^post_max_size\s=.*/post_max_size = ${MAX_UPLOAD_FILESIZE}M/" $PHP_INI_DIR/php.ini

COPY --from=mwader/static-ffmpeg:4.3.2 /ffmpeg /usr/local/bin/
COPY --from=mwader/static-ffmpeg:4.3.2 /ffprobe /usr/local/bin/

USER nobody:nogroup
