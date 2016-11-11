IMAGE_ID := "myownradio/backend-service"
CONTAINER_ID := "myownradio-backend-service"
BIN_DIR := $(shell npm bin)

install:
	mkdir -p storage/cache
	mkdir -p storage/logs
	mkdir -p storage/sessions
	composer install
	npm install
	$(BIN_DIR)/bower install
	$(BIN_DIR)/gulp scripts

serve:
	php -S 127.0.0.1:6060 server.php
