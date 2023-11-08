#!/usr/bin/env php
<?php

use ToyWpRouting\Rewrite;
use ToyWpRouting\RewriteCollection;
use ToyWpRouting\RewriteCollectionCache;

require_once __DIR__ . '/../vendor/autoload.php';


// Cached rewrite collections for unit tests.

// Regular
$rewriteCollection = new RewriteCollection();

$rewriteCollection->add(new Rewrite(['GET', 'HEAD'], '^first$', 'index.php?var=first', ['var' => 'var'], 'firsthandler'));
$rewriteCollection->add(new Rewrite(['POST'], '^second$', 'index.php?var=second', ['var' => 'var'], 'secondhandler'))->setIsActiveCallback('secondisactivecallback');

$rewriteCollectionCache = new RewriteCollectionCache(__DIR__ . '/../tests/fixtures', 'rewrite-cache.php');
$rewriteCollectionCache->put($rewriteCollection);

// Closures
$rewriteCollection = new RewriteCollection('pfx_');

$rewriteCollection->add(new Rewrite(['GET', 'HEAD'], '^regex$', 'index.php?pfx_var=val', ['pfx_var' => 'var'], function () {}, 'pfx_'))->setIsActiveCallback(function () {});

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
