#!/usr/bin/env bash

PHP_VERSION=$1
WP_VERSION=$2

if [[ $WP_VERSION == "latest" ]]; then
    echo "{ \"phpVersion\": \"$PHP_VERSION\" }" > .wp-env.override.json
else
    echo "{ \"core\": \"WordPress/WordPress#$WP_VERSION\", \"phpVersion\": \"$PHP_VERSION\" }" > .wp-env.override.json
fi
