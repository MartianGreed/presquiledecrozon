start:
	docker-compose -p presquiledecrozon up -d
	symfony serve

fixtures:
	php bin/console doctrine:fixtures:load --append

test:
	vendor/bin/phpunit

coverage:
	vendor/bin/phpunit --coverage-html coverage

phpstan:
	vendor/bin/phpstan analyse -c phpstan.neon

lint:
	vendor/bin/rector process src tests
	vendor/bin/ecs check src tests --fix