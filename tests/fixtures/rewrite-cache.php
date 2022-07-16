<?php

declare(strict_types=1);

return new class extends \ToyWpRouting\RewriteCollection
{
    public function __construct()
    {
        parent::__construct('');

        $rewrite0 = new \ToyWpRouting\OptimizedRewrite(array (
  0 => 'GET',
  1 => 'HEAD',
), array (
  0 => new \ToyWpRouting\OptimizedRewriteRule('9f79cebcf1735d5eaefeee8dbc7316dd', array (
  'var' => 'first',
  'matchedRule' => '9f79cebcf1735d5eaefeee8dbc7316dd',
), 'index.php?var=first&matchedRule=9f79cebcf1735d5eaefeee8dbc7316dd', array (
  'var' => 'first',
  'matchedRule' => '9f79cebcf1735d5eaefeee8dbc7316dd',
), array (
  'var' => 'var',
  'matchedRule' => 'matchedRule',
), '^first$'),
), array (
  'var' => 'var',
  'matchedRule' => 'matchedRule',
), 'firsthandler', NULL);
$this->rewrites->attach($rewrite0);
$rewrite1 = new \ToyWpRouting\OptimizedRewrite(array (
  0 => 'POST',
), array (
  0 => new \ToyWpRouting\OptimizedRewriteRule('3cf5d427e03a68a3881d2d68a86b64f1', array (
  'var' => 'second',
  'matchedRule' => '3cf5d427e03a68a3881d2d68a86b64f1',
), 'index.php?var=second&matchedRule=3cf5d427e03a68a3881d2d68a86b64f1', array (
  'var' => 'second',
  'matchedRule' => '3cf5d427e03a68a3881d2d68a86b64f1',
), array (
  'var' => 'var',
  'matchedRule' => 'matchedRule',
), '^second$'),
), array (
  'var' => 'var',
  'matchedRule' => 'matchedRule',
), 'secondhandler', 'secondisactivecallback');
$this->rewrites->attach($rewrite1);

        $this->rewriteRules = array (
  '^first$' => 'index.php?var=first&matchedRule=9f79cebcf1735d5eaefeee8dbc7316dd',
  '^second$' => 'index.php?var=second&matchedRule=3cf5d427e03a68a3881d2d68a86b64f1',
);
        $this->queryVariables = array (
  'var' => 'var',
  'matchedRule' => 'matchedRule',
);

        $this->rewritesByRegexHashAndMethod = array (
  '9f79cebcf1735d5eaefeee8dbc7316dd' => 
  array (
    'GET' => $rewrite0,
    'HEAD' => $rewrite0,
  ),
  '3cf5d427e03a68a3881d2d68a86b64f1' => 
  array (
    'POST' => $rewrite1,
  ),
);

        $this->locked = true;
    }
};
