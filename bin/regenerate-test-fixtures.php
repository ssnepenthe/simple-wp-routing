#!/usr/bin/env php
<?php

use ToyWpRouting\RewriteCollection;
use ToyWpRouting\RewriteCollectionCache;

require_once __DIR__ . '/../vendor/autoload.php';


// Cached rewrite collections for unit tests.

// Regular
$rewriteCollection = new RewriteCollection();

$rewriteCollection->get('^first$', 'index.php?var=first', 'firsthandler');
$rewriteCollection->post('^second$', 'index.php?var=second', 'secondhandler')->setIsActiveCallback('secondisactivecallback');

$rewriteCollectionCache = new RewriteCollectionCache(__DIR__ . '/../tests/fixtures', 'rewrite-cache.php');
$rewriteCollectionCache->put($rewriteCollection);

// Closures
$rewriteCollection = new RewriteCollection('pfx_');

$rewriteCollection->get('^regex$', 'index.php?var=val', function () {})->setIsActiveCallback(function () {});

$rewriteCollectionCache = new RewriteCollectionCache(__DIR__ . '/../tests/fixtures', 'rewrite-cache-serialized-closures.php');
$rewriteCollectionCache->put($rewriteCollection);


// Cached rewrite collections for browser tests.

use TwrTestPlugin\TestGroup;

require_once __DIR__ . '/../tests/fixtures/twr-test-plugin/test-groups.php';

foreach (TestGroup::createTestGroups() as $testGroup) {
    $cache = $testGroup->createCache();

    $cache->delete();

    $cache->put($testGroup->createRewrites());
}
