.DEFAULT_GOAL := help
.PHONY: help csfixer phpstan installdeps updatedeps composer test

PHP_IMAGE := php:8.5-cli
DOCKER_VOLUME := -v "$(PWD)":/app -w /app
DOCKER_RUN := docker run --rm -it $(DOCKER_VOLUME) $(PHP_IMAGE)

help: ## Show available commands
	@echo ""
	@echo "Available make commands:"
	@echo ""
	@grep -E '(^[a-zA-Z0-9\./_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) \
		| awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' \
		| sed -e 's/\[32m##/[33m/'

installdeps: ## Install Composer dependencies
	$(DOCKER_RUN) bash -c "apt update && apt install -y unzip git zip curl > /dev/null && \
		curl -sS https://getcomposer.org/installer | php && \
		php composer.phar install"

updatedeps: ## Update Composer dependencies
	$(DOCKER_RUN) bash -c "php composer.phar update"

composer: ## Run composer command (usage: make composer ARGS='update')
	$(DOCKER_RUN) php composer.phar $(ARGS)

csfixer: ## Run PHP-CS-Fixer on src and tests
	$(DOCKER_RUN) vendor/bin/php-cs-fixer fix src --rules=@Symfony --verbose
	$(DOCKER_RUN) vendor/bin/php-cs-fixer fix tests --rules=@Symfony --verbose

phpstan: ## Run PHPStan static analysis
	$(DOCKER_RUN) vendor/bin/phpstan analyse src tests -c phpstan.neon

test: ## Run PHPUnit
	$(DOCKER_RUN) vendor/bin/phpunit