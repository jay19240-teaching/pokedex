setup:
	@make build
	@make up
	@make composer-update

build:
	docker-compose build --no-cache --force-rm
	docker build -t tests-api ./Tests-api
stop:
	docker-compose stop
up:
	docker-compose up -d
composer-update:
	docker exec laravel-docker bash -c "composer update"
data:
	docker exec laravel-docker bash -c "php artisan migrate"
	docker exec laravel-docker bash -c "php artisan db:seed"