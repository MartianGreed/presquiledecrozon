start:
	podman compose -p presquiledecrozon up -d
	symfony serve -d

create_db:
	symfony console doctrine:database:create

migrate:
	symfony console doctrine:migration:migrate

stop:
	podman compose -p presquiledecrozon stop
	symfony server:stop

fixtures:
	symfony console doctrine:fixtures:load --group=data
	symfony console doctrine:fixtures:load --group=rental --append

test:
	vendor/bin/phpunit

test_e2e:
	APP_ENV=test symfony server:start --no-tls -d
	vendor/bin/phpunit --group=e2e
	symfony server:stop

test_e2e_setup:
	symfony console doctrine:database:drop --if-exists --force --env=test
	symfony console doctrine:database:create --env=test
	symfony console doctrine:migrations:migrate --env=test

test_e2e_full: test_e2e_setup test_e2e

format:
	vendor/bin/php-cs-fixer fix

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
