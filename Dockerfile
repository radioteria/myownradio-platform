FROM myownradio/backend-runtime

MAINTAINER Roman Lakhtadyr <roman.lakhtadyr@gmail.com>

WORKDIR /usr/app/

# Install application dependencies
COPY composer.json composer.lock ./
RUN composer install --no-plugins --no-scripts --no-dev

# Install application
COPY . ./

ARG GIT_CURRENT_COMMIT="<unknown>"
ENV GIT_CURRENT_COMMIT=${GIT_CURRENT_COMMIT}
