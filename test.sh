#!/usr/bin/env bash

# Runing tests for Lumen
cd lumen-test || return

if [ ! -f codecept.phar ]; then
    wget http://codeception.com/codecept.phar 2>/dev/null || curl -LsS -O http://codeception.com/codecept.phar
fi

php codecept.phar run \
    && mkdir routes \
    && sh clean.sh