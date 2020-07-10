.PHONY: test coverage style-check static-analysis help

.DEFAULT_GOAL := help

test: ## Run tests
	@printf "\033[93m→ Running tests\033[0m\n"
	@bin/phpunit --testdox
	@printf "\n\033[92m✔︎ Tests are completed\033[0m\n"

coverage: ## Run tests and generate the code coverage report
	@printf "\033[93m→ Running tests and generating the code coverage report\033[0m\n"
	@bin/phpunit --testdox --coverage-text
	@printf "\n\033[92m✔︎ Tests are completed and the report is generated\033[0m\n"

style-check: ## Check the code style
	@printf "\033[93m→ Checking the code style\033[0m\n"
	@bin/php-cs-fixer fix --allow-risky=yes --dry-run --diff-format=udiff --show-progress=dots --verbose
	@printf "\n\033[92m✔︎ Code style is checked\033[0m\n"

static-analysis: ## Do static code analysis
	@printf "\033[93m→ Analysing the code\033[0m\n"
	@bin/phpstan analyse
	@printf "\n\033[92m✔︎ Code static analysis is completed\033[0m\n"

help: ## Show help
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'
