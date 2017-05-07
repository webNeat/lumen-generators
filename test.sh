#!/usr/bin/env bash

# Runing tests for Lumen
cd lumen-test || return

if [ ! -f codecept.phar ]; then
    wget http://codeception.com/codecept.phar
fi
php codecept.phar run