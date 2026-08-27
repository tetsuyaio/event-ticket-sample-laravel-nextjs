.PHONY: up down build migrate seed test test-api test-web lint

up:
	docker compose up --build

down:
	docker compose down

build:
	docker compose build

migrate:
	docker compose run --rm api php artisan migrate

seed:
	docker compose run --rm api php artisan db:seed

test: test-api test-web

test-api:
	docker compose run --rm -e DB_CONNECTION=sqlite -e DB_DATABASE=:memory: api sh -c 'cp .env.example .env && php artisan key:generate --force --no-ansi && php artisan test'

test-web:
	cd apps/web && pnpm test

lint:
	docker compose run --rm api ./vendor/bin/pint --test
	cd apps/web && pnpm lint
