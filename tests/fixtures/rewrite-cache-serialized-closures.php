<?php

declare(strict_types=1);

use SimpleWpRouting\Dumper\OptimizedRewrite;
use SimpleWpRouting\Dumper\OptimizedRewriteCollection;

if (! class_exists('CachedRewriteCollectiond870e2f0a516a50e9661e34d430b2e666e47c2a2b39fe2cc18c9a4db424fbee4')) {
    class CachedRewriteCollectiond870e2f0a516a50e9661e34d430b2e666e47c2a2b39fe2cc18c9a4db424fbee4 extends OptimizedRewriteCollection
    {
        public function __construct()
        {
            $this->queryVariables = array (
  'pfx_var' => 'var',
);
            $this->rewriteRules = array (
  '^regex$' => 'index.php?pfx_var=val',
);

            $rewrite0 = new OptimizedRewrite(array (
  0 => 'GET',
  1 => 'HEAD',
), '^regex$', 'index.php?pfx_var=val', array (
  'pfx_var' => 'var',
), static function () {}, static function () {});
$this->rewritesByRegexAndMethod = array (
  '^regex$' => 
  array (
    'GET' => $rewrite0,
    'HEAD' => $rewrite0,
  ),
);
        }
    }
}

return new CachedRewriteCollectiond870e2f0a516a50e9661e34d430b2e666e47c2a2b39fe2cc18c9a4db424fbee4();
