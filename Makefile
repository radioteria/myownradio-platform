test:
	composer exec phpunit

install:
	mkdir -p storage/logs
	mkdir -p storage/sessions
	mkdir -p storage/images/avatars
	mkdir -p storage/images/covers
	composer install
	bower install
