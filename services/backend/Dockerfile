# syntax=docker/dockerfile:1.4.3-labs
FROM php:7.4-fpm-alpine as php-base

MAINTAINER Roman Lakhtadyr <roman.lakhtadyr@gmail.com>

ARG MAX_UPLOAD_FILESIZE=256
ENV MAX_UPLOAD_FILESIZE=${MAX_UPLOAD_FILESIZE}

RUN apk add git curl-dev libpng-dev oniguruma-dev jpeg-dev freetype-dev

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
	&& docker-php-ext-install -j$(nproc) gd pdo_mysql

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" && \
    rm "$PHP_INI_DIR/php.ini-development" && \
    sed -i "s/^upload_max_filesize\s=.*/upload_max_filesize = ${MAX_UPLOAD_FILESIZE}M/" $PHP_INI_DIR/php.ini && \
    sed -i "s/^variables_order\s=.*/variables_order = \"EGPCS\"/" $PHP_INI_DIR/php.ini && \
    sed -i "s/^post_max_size\s=.*/post_max_size = ${MAX_UPLOAD_FILESIZE}M/" $PHP_INI_DIR/php.ini

# Setup PHP-FPM config settings
RUN echo 'pm.max_children = 500' >> /usr/local/etc/php-fpm.d/zz-docker.conf && \
    echo 'pm.start_servers = 100' >> /usr/local/etc/php-fpm.d/zz-docker.conf && \
    echo 'pm.min_spare_servers = 100' >> /usr/local/etc/php-fpm.d/zz-docker.conf && \
    echo 'pm.max_spare_servers = 200' >> /usr/local/etc/php-fpm.d/zz-docker.conf && \
    echo 'pm.max_requests = 500' >> /usr/local/etc/php-fpm.d/zz-docker.conf

COPY --from=composer:1.10.27 /usr/bin/composer /usr/local/bin/
COPY --from=mwader/static-ffmpeg:5.0.1-3 /ffmpeg /usr/local/bin/
COPY --from=mwader/static-ffmpeg:5.0.1-3 /ffprobe /usr/local/bin/

USER nobody:nogroup


FROM nginx:1.23.3-alpine as nginx-base

ARG MAX_UPLOAD_FILESIZE=256
ENV MAX_UPLOAD_FILESIZE=${MAX_UPLOAD_FILESIZE}

ARG PHP_FPM_HOSTNAME=backend-php-fpm
ENV PHP_FPM_HOSTNAME=${PHP_FPM_HOSTNAME}

COPY <<EOF /etc/nginx/templates/status.conf.template
server {
    listen 80;
    server_name local
    root /code/public;
    index index.php;

    client_max_body_size ${MAX_UPLOAD_FILESIZE}M;

    location / {
        try_files $uri $uri/ @rewrites;
        error_page 403 = @rewrites;
        error_page 404 = @rewrites;
        error_page 405 = @rewrites;
    }

    location @rewrites {
        rewrite ^(.+)$ /index.php last;
    }

    location = /index.php {
       fastcgi_param  ENVIRONMENT "production";
       fastcgi_pass   ${PHP_FPM_HOSTNAME}:9000;
       fastcgi_param  SCRIPT_FILENAME /code/public/$fastcgi_script_name;
       include        fastcgi_params;
       send_timeout   3600s;
    }

    # block access to hidden files .git or .htaccess
    location ~ /\. {
        return 404;
    }

    access_log /proc/self/fd/2 main;
    error_log /proc/self/fd/2 notice;
}
EOF

COPY <<EOF /etc/nginx/templates/status.conf.template
server {
    listen       8080;
    server_name  localhost;

    location = /stub_status {
        stub_status;
    }
}
EOF

EXPOSE 80


FROM php-base as php-devenv

USER 0:0

RUN apk --update add bash nodejs npm
