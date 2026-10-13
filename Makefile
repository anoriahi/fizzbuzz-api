export HOST_UID := $(shell id -u)
export HOST_GID := $(shell id -g)

DC      = docker compose
DC_PROD = docker compose -f compose.yaml -f compose.prod.yaml
PHP     = $(DC) exec php

.DEFAULT_GOAL := help
.PHONY: help build up down restart logs sh composer console db rector cs cs-fix stan qa test prod-build prod-up prod-down

help: ## Liste des commandes
	@grep -E '^[a-zA-Z_-]+:.*?## ' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-12s\033[0m %s\n", $$1, $$2}'

## —— Dev ————————————————————————————————————
build: ## Construit les images de dev
	$(DC) build --pull

up: ## Démarre l'environnement de dev
	@mkdir -p app # sinon Docker crée ./app en root au montage
	$(DC) up -d --wait

down: ## Arrête l'environnement de dev
	$(DC) down --remove-orphans

restart: down up ## Redémarre l'environnement de dev

logs: ## Suit les logs
	$(DC) logs -f

sh: ## Shell dans le conteneur PHP
	$(PHP) sh

composer: ## Lance composer, ex: make composer c="require symfony/serializer"
	$(PHP) composer $(c)

console: ## Lance bin/console, ex: make console c="debug:router"
	$(PHP) bin/console $(c)

db: ## Client MariaDB sur la base de l'application
	$(DC) exec database sh -c 'mariadb -u"$$MARIADB_USER" -p"$$MARIADB_PASSWORD" "$$MARIADB_DATABASE"'

## —— Qualité ————————————————————————————————
rector: ## Rector à blanc (propose les modifications)
	$(PHP) vendor/bin/rector process --dry-run

cs: ## PHP-CS-Fixer à blanc (affiche les différences)
	$(PHP) vendor/bin/php-cs-fixer fix --dry-run --diff

cs-fix: ## Applique PHP-CS-Fixer
	$(PHP) vendor/bin/php-cs-fixer fix

stan: ## Analyse statique PHPStan
	$(PHP) vendor/bin/phpstan analyse --memory-limit=1G

qa: rector cs stan ## Toutes les vérifications (sans modification)

test: ## Tests PHPUnit sur la base fizzbuzz_test, ex: make test c="--filter FizzBuzz"
	$(PHP) bin/console doctrine:migrations:migrate --no-interaction --env=test --quiet
	$(PHP) bin/phpunit $(c)

## —— Prod ———————————————————————————————————
prod-build: ## Construit les images de prod
	$(DC_PROD) build --pull

prod-up: ## Démarre en prod (APP_SECRET, DB_PASSWORD, DB_ROOT_PASSWORD requis)
	$(DC_PROD) up -d --wait

prod-down: ## Arrête la prod
	$(DC_PROD) down --remove-orphans
