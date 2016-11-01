deps:
	composer install

test:
	vendor/bin/phpunit -c phpunit.xml
