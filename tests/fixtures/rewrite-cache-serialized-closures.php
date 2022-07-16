<?php

declare(strict_types=1);

return new class extends \ToyWpRouting\RewriteCollection
{
    public function __construct()
    {
        parent::__construct('pfx_');

        $rewrite0 = new \ToyWpRouting\OptimizedRewrite(array (
  0 => 'GET',
  1 => 'HEAD',
), array (
  0 => new \ToyWpRouting\OptimizedRewriteRule('e8362b7488c4e1a7eee5ff88b032f6eb', array (
  'pfx_var' => 'val',
  'pfx_matchedRule' => 'e8362b7488c4e1a7eee5ff88b032f6eb',
), 'index.php?pfx_var=val&pfx_matchedRule=e8362b7488c4e1a7eee5ff88b032f6eb', array (
  'var' => 'val',
  'matchedRule' => 'e8362b7488c4e1a7eee5ff88b032f6eb',
), array (
  'pfx_var' => 'var',
  'pfx_matchedRule' => 'matchedRule',
), '^regex$'),
), array (
  'pfx_var' => 'var',
  'pfx_matchedRule' => 'matchedRule',
), static function () {}, static function () {});
$this->rewrites->attach($rewrite0);

        $this->rewriteRules = array (
  '^regex$' => 'index.php?pfx_var=val&pfx_matchedRule=e8362b7488c4e1a7eee5ff88b032f6eb',
);
        $this->queryVariables = array (
  'pfx_var' => 'var',
  'pfx_matchedRule' => 'matchedRule',
);

        $this->rewritesByRegexHashAndMethod = array (
  'e8362b7488c4e1a7eee5ff88b032f6eb' => 
  array (
    'GET' => $rewrite0,
    'HEAD' => $rewrite0,
  ),
);

        $this->locked = true;
    }
};
