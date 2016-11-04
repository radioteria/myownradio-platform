test:
	composer exec phpunit

install:
	mkdir -p storage/logs
	mkdir -p storage/sessions
	composer install
	bower install
