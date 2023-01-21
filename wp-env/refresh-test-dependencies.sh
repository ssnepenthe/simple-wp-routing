#!/usr/bin/env bash

COMP_WORK_DIR="--working-dir=./tests/fixtures/twr-test-plugin"

if [[ -d ./tests/fixtures/twr-test-plugin/vendor ]]; then
    composer reinstall ssnepenthe/toy-wp-routing $COMP_WORK_DIR
else
    composer update $COMP_WORK_DIR
fi
