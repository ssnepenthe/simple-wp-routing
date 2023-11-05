#!/usr/bin/env php
<?php

use ToyWpRouting\Rewrite;
use ToyWpRouting\RewriteCollection;
use ToyWpRouting\RewriteCollectionCache;
use ToyWpRouting\RewriteRule;

require_once __DIR__ . '/../vendor/autoload.php';


// Cached rewrite collections for unit tests.

// Regular
$rewriteCollection = new RewriteCollection();

$ruleOne = new RewriteRule('^first$', 'index.php?var=first');
$ruleOne->setRequiredQueryVariables(['var']);
$ruleTwo = new RewriteRule('^second$', 'index.php?var=second');
$ruleTwo->setRequiredQueryVariables(['var']);

$rewriteCollection->add(new Rewrite(['GET', 'HEAD'], [$ruleOne], 'firsthandler'));
$rewriteCollection->add(new Rewrite(['POST'], [$ruleTwo], 'secondhandler'))->setIsActiveCallback('secondisactivecallback');

$rewriteCollectionCache = new RewriteCollectionCache(__DIR__ . '/../tests/fixtures', 'rewrite-cache.php');
$rewriteCollectionCache->put($rewriteCollection);

// Closures
$rewriteCollection = new RewriteCollection('pfx_');

$ruleThree = new RewriteRule('^regex$', 'index.php?var=val', 'pfx_');
$ruleThree->setRequiredQueryVariables(['pfx_var']);
$rewriteCollection->add(new Rewrite(['GET', 'HEAD'], [$ruleThree], function () {}))->setIsActiveCallback(function () {});

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
