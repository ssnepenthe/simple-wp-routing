#!/usr/bin/env php
<?php

use TwrTestPlugin\TestGroup;

require_once __DIR__ . '/../../../../vendor/autoload.php';
require_once __DIR__ . '/../test-groups.php';

foreach (TestGroup::createTestGroups() as $testGroup) {
    $cache = $testGroup->createCache();

    $cache->delete();

    $cache->put($testGroup->createRewrites());
}
