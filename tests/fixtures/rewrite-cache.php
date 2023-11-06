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
  'var' => 'var',
);
            $this->rewriteRules = array (
  '^first$' => 'index.php?var=first',
  '^second$' => 'index.php?var=second',
);

            $rewrite0 = new \ToyWpRouting\Compiler\OptimizedRewrite(array (
  0 => 'GET',
  1 => 'HEAD',
), '^first$', 'index.php?var=first', array (
  'var' => 'var',
), 'firsthandler', NULL);
$rewrite1 = new \ToyWpRouting\Compiler\OptimizedRewrite(array (
  0 => 'POST',
), '^second$', 'index.php?var=second', array (
  'var' => 'var',
), 'secondhandler', 'secondisactivecallback');
$this->rewrites->attach($rewrite0);
$this->rewrites->attach($rewrite1);
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
    };
};
