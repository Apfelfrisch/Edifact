# Prefer a PHP 8.5 binary: php8.5 from PATH, the static fallback, then plain php.
PHP ?= $(shell command -v php8.5 2>/dev/null \
	|| { test -x $(HOME)/.local/opt/php85/php && echo $(HOME)/.local/opt/php85/php; } \
	|| command -v php)

.PHONY: it format lint analyze test

it: format lint analyze test

format:
	$(PHP) vendor/bin/mago fmt

lint:
	$(PHP) vendor/bin/mago lint

analyze:
	$(PHP) vendor/bin/mago analyze

test:
	$(PHP) vendor/bin/phpunit
