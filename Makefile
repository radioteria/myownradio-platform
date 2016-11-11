IMAGE_ID="myownradio/backend-service"
CONTAINER_ID="myownradio-backend-service"

install:
	mkdir -p storage/cache
	mkdir -p storage/logs
	mkdir -p storage/sessions
	composer install
	bower install

serve:
	php -S 127.0.0.1:6060 server.php
