start:
	docker-compose -p presquiledecrozon up -d
	symfony serve

stop:
	docker-compose -p presquiledecrozon stop

fixtures:
	php bin/console doctrine:fixtures:load --group=data
	php bin/console doctrine:fixtures:load --group=rental --append

test:
	vendor/bin/phpunit

test_class:
	vendor/bin/phpunit --filter $(filter)

coverage:
	vendor/bin/phpunit --coverage-html coverage

phpstan:
	vendor/bin/phpstan analyse -c phpstan.neon --memory-limit=512M

lint:
	vendor/bin/rector process src tests
	vendor/bin/ecs check src tests --fix

prod_vendor:
	symfony composer install --no-ansi --no-dev --no-interaction --no-plugins --no-progress --no-scripts --optimize-autoloader

deploy_assets_staging:
	npm run build:staging
	php bin/console app:assets:upload

deploy_staging:
	composer install --no-ansi --no-dev --no-interaction --no-plugins --no-progress --no-scripts --optimize-autoloader
	php bin/console cache:clear --env prod && php bin/console cache:warmup --env prod
	serverless deploy

deploy_prod:
	npm run build:prod
	php bin/console app:assets:upload
	serverless deploy --stage prod