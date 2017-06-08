FROM debian:jessie-backports

MAINTAINER Roman Lakhtadyr <roman.lakhtadyr@gmail.com>

ENV DEBIAN_FRONTEND=noninteractive
ENV PHP_VERSION=7.1

# Install utilities
RUN apt-get update && \
    apt-get install -y apt-utils curl apt-transport-https git

# Install Node.js, web server and media applications
RUN (curl -sL https://deb.nodesource.com/setup_8.x | bash) && \
    apt-get install -y ffmpeg mediainfo nodejs build-essential nginx supervisor

# Install latest PHP and Composer
RUN (curl -sL https://packages.sury.org/php/apt.gpg | apt-key add -) && \
    (echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" > /etc/apt/sources.list.d/php.list) && \
    apt-get update && \
    apt-get install -y php-fpm php-cli php-mbstring php-mysql php-xml php-gd php-mcrypt php-curl php-zip && \
    mkdir -p /var/run/php && \
    (curl -sL https://getcomposer.org/installer | php -- --install-dir=bin --filename=composer) && \
    sed -i 's/^upload_max_filesize\s=.*/upload_max_filesize = 100M/' /etc/php/$PHP_VERSION/fpm/php.ini && \
    sed -i 's/^post_max_size\s=.*/post_max_size = 100M/' /etc/php/$PHP_VERSION/fpm/php.ini

COPY ./cn/supervisord.conf /etc/supervisor/supervisord.conf
COPY ./cn/nginx-fpm.conf /etc/nginx/sites-available/nginx-fpm.conf
COPY ./cn/nginx-upload.conf /etc/nginx/conf.d/nginx-upload.conf

RUN rm -f /etc/nginx/sites-enabled/* && \
    ln -s /etc/nginx/sites-available/nginx-fpm.conf /etc/nginx/sites-enabled/nginx-fpm.conf

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/supervisord.conf"]

# Install application
WORKDIR /usr/app/
COPY . ./
RUN composer install --no-plugins --no-scripts --no-dev

VOLUME /var/lib/php/sessions
VOLUME /tmp

EXPOSE 6060
