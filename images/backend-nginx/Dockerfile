FROM node:15

WORKDIR /code

COPY package.json package-lock.json ./
RUN npm ci

COPY bower.json ./
RUN npx bower install --allow-root

COPY . ./

RUN npm run build


FROM myownradio/backend-nginx-base:1.0.2

WORKDIR /code

COPY --from=0 /code /code
