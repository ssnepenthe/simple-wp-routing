<?php

declare(strict_types=1);

use SimpleWpRouting\Dumper\OptimizedRewrite;
use SimpleWpRouting\Dumper\OptimizedRewriteCollection;

if (! class_exists('CachedRewriteCollectionf767d562c1ab18b24e14886de398917e213c07bbc06638ecbfb52961499c9490')) {
    class CachedRewriteCollectionf767d562c1ab18b24e14886de398917e213c07bbc06638ecbfb52961499c9490 extends OptimizedRewriteCollection
    {
        public function __construct()
        {
            $this->queryVariables = array (
  'var' => 'var',
);
            $this->rewriteRules = array (
  '^first$' => 'index.php?var=first',
  '^second$' => 'index.php?var=second',
);

            $rewrite0 = new OptimizedRewrite(array (
  0 => 'GET',
  1 => 'HEAD',
), '^first$', 'index.php?var=first', array (
  'var' => 'var',
), 'firsthandler', NULL);
$rewrite1 = new OptimizedRewrite(array (
  0 => 'POST',
), '^second$', 'index.php?var=second', array (
  'var' => 'var',
), 'secondhandler', 'secondisactivecallback');
$this->rewritesByRegexAndMethod = array (
  '^first$' => 
  array (
    'GET' => $rewrite0,
    'HEAD' => $rewrite0,
  ),
  '^second$' => 
  array (
    'POST' => $rewrite1,
  ),
);
        }
    }
}

return new CachedRewriteCollectionf767d562c1ab18b24e14886de398917e213c07bbc06638ecbfb52961499c9490();
