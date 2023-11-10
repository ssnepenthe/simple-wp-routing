<?php

declare(strict_types=1);

return function (): \ToyWpRouting\RewriteCollection {
    return new class() extends \ToyWpRouting\RewriteCollection
    {
        protected bool $locked = true;

        public function __construct()
        {
            $this->queryVariables = array (
  'pfx_var' => 'var',
);
            $this->rewriteRules = array (
  '^regex$' => 'index.php?pfx_var=val',
);

            $rewrite0 = new \ToyWpRouting\Dumper\OptimizedRewrite(array (
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

        public function getRewrites(): array
        {
            throw new LogicException('Rewrites list not accessible on cache rewrite collection');
        }
    };
};
