FROM nginx:stable-alpine

ARG MAX_UPLOAD_FILESIZE=256
ENV MAX_UPLOAD_FILESIZE=${MAX_UPLOAD_FILESIZE}

COPY default.conf.template /etc/nginx/templates/

EXPOSE 8080
