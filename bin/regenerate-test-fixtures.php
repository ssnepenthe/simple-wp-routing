#!/usr/bin/env php
<?php

use ToyWpRouting\RewriteCollection;
use ToyWpRouting\RewriteCollectionCache;

require_once __DIR__ . '/../vendor/autoload.php';

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
