FROM node:15

WORKDIR /code

COPY package.json package-lock.json bower.json ./
RUN npm ci

COPY . ./

RUN npm run build


FROM myownradio/backend-nginx-base:1.0.2

WORKDIR /code

COPY --from=0 /code /code
