#!/usr/bin/env bash

npx wp-env run tests-wordpress "chmod -cR ugo+w /var/www/html/wp-content/plugins/twr-test-plugin/var/cache"
npx wp-env run tests-wordpress "php /var/www/html/wp-content/plugins/twr-test-plugin/bin/regenerate-caches.php"
