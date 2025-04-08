EXEC_PHP        = php -d memory_limit=-1
CONSOLE         = $(EXEC_PHP) bin/console
COMPOSER        = composer
SYMFONY         = symfony

##
##Dev
##-------------

nix: ## Start nix development
	nix develop --extra-experimental-features nix-command --extra-experimental-features flakes

docker: ## Start docker development
	docker-compose up -d

encore: ## Start encore dev server
	if [ ! -d "node_modules" ]; then npm install; fi
	npm run dev

env: ## Start symfony dev server
	$(SYMFONY) server:start --port=8092

test: ## testing application
	./bin/phpunit

##
##Symfony
##-------------

install: ## Install (DB/Schema/fixtures)
	$(COMPOSER) install
	$(CONSOLE) doctrine:database:create
	$(CONSOLE) doctrine:schema:create
	$(CONSOLE) doctrine:fixtures:load -vvv --no-interaction
	$(CONSOLE) cache:clear --env=prod

fixtures: ## Replay fixtures
	$(CONSOLE) cache:clear
	$(CONSOLE) doctrine:database:drop --force
	$(CONSOLE) doctrine:database:create
	$(CONSOLE) doctrine:schema:update --force
	$(CONSOLE) doctrine:fixtures:load --env=dev --no-interaction

reload-db: ## Reload initial db
	$(CONSOLE) doctrine:schema:drop --force
	#bzip2 -dk ./db/dump.sql.bz2
	mysql -h 127.0.0.1 -P 3311 -u darkwood -pdarkwood darkwood < ./db/dump.sql
	rm ./db/dump.sql

schema: ## Update database schema
	$(CONSOLE) doctrine:schema:update --force

update: ## Stop the crap and start working
	$(COMPOSER) install
	$(CONSOLE) doctrine:schema:update --force
	#$(CONSOLE) search:clear
	#$(CONSOLE) search:import
	$(CONSOLE) cache:clear
	$(CONSOLE) cache:clear --env=prod
	php bin/opcache.php

cache: .env vendor
	$(CONSOLE) cache:clear

keys: ## generate keys for JWT
	php bin/console lexik:jwt:generate-keypair

assets: ## build and install assets
	npm run build
	bin/console asset:install

##
##DevOps
##-------------

php-cs-fixer: ## Check and fix coding styles using PHP CS Fixer
	composer php-cs-fixer

phpstan: ## Execute PHPStan analysis
	composer phpstan

phpunit: ## Launch PHPUnit test suite
	composer phpunit

deploy: ## Deploy app on server
	npm install
	npm run build
	composer install --no-dev --optimize-autoloader
	bin/console doctrine:migration:migrate --no-interaction

# DEFAULT
.DEFAULT_GOAL := help
help:
	@grep -E '(^[a-zA-Z_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'
.PHONY: help

##
