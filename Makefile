start:
	docker-compose -p presquiledecrozon up -d
	symfony serve

fixtures:
	php bin/console doctrine:fixtures:load --group=data
	php bin/console doctrine:fixtures:load --group=rental --append

test:
	vendor/bin/phpunit

coverage:
	vendor/bin/phpunit --coverage-html coverage

phpstan:
	vendor/bin/phpstan analyse -c phpstan.neon --memory-limit=512M

lint:
	vendor/bin/rector process src tests
	vendor/bin/ecs check src tests --fix