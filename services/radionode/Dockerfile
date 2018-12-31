FROM node:11 as dev
ENV NODE_ENV=development
RUN apt-get update && apt-get install -y libav-tools ffmpeg && apt-get autoremove -y && apt-get clean -y

FROM dev as build
ENV NODE_ENV=development
WORKDIR /usr/src/app
COPY package.json ./
COPY package-lock.json ./
RUN npm install
COPY . ./
RUN npm run build

FROM dev
ENV NODE_ENV=production
WORKDIR /usr/src/app
COPY package.json ./
COPY package-lock.json ./
RUN npm install
COPY ./files/ /usr/src/app/files
COPY --from=build /usr/src/app/dist/ /usr/src/app/dist/
CMD npm start
