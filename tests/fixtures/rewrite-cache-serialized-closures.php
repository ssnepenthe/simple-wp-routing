<?php

declare(strict_types=1);

return function (): \ToyWpRouting\RewriteCollection {
    return new class() extends \ToyWpRouting\RewriteCollection
    {
        protected bool $locked = true;

        public function __construct()
        {
            parent::__construct();

            $this->queryVariables = array (
  'pfx_var' => 'var',
);
            $this->rewriteRules = array (
  '^regex$' => 'index.php?pfx_var=val',
);

            $rewrite0 = new \ToyWpRouting\Compiler\OptimizedRewrite(array (
  0 => 'GET',
  1 => 'HEAD',
), array (
  'pfx_var' => 'var',
), array (
  0 => 'pfx_var',
), static function () {}, static function () {});
$this->rewrites->attach($rewrite0);
$this->rewritesByRegexAndMethod = array (
  '^regex$' => 
  array (
    'GET' => $rewrite0,
    'HEAD' => $rewrite0,
  ),
);
        }
    };
};
