FROM node:11 as dev

ENV NODE_ENV=development

RUN apt-get update && apt-get install -y libav-tools ffmpeg && apt-get autoremove -y && apt-get clean -y
