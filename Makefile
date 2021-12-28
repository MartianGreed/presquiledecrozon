start:
	docker-compose -p presquiledecrozon up -d
	symfony serve

fixtures:
	php bin/console doctrine:fixtures:load --append