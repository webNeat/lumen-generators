#!/usr/bin/env bash

# installing dependencies for lumen-generators
composer install --no-interaction
composer update --no-interaction

# installing dependencies for lumen-test
cd lumen-test && composer install --no-interaction
