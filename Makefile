PHP_CONTAINER := symfony_php

run:
	docker-compose up -d --build

bash:
	docker exec -it $(PHP_CONTAINER) bash

console:
	docker exec -it $(PHP_CONTAINER) php /var/www/html/bin/console

stop:
	docker-compose down

logs:
	docker-compose logs -f

author:
	docker exec -it $(PHP_CONTAINER) php /var/www/html/bin/console app:author:create

composer-install:
	docker exec -it $(PHP_CONTAINER) composer install

