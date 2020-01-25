FROM myownradio/backend-image:latest

WORKDIR /usr/app

COPY composer.json composer.lock ./
RUN composer install --no-plugins --no-scripts --no-dev

COPY package.json package-lock.json ./
RUN npm ci

COPY bower.json ./
RUN npx bower install --allow-root

COPY . ./

RUN npx gulp scripts
