<?php

return array (
  0 => 
  array (
    'methods' => 
    array (
      0 => 'GET',
    ),
    'rewriteRules' => 
    array (
      'first' => 'index.php?first=first&matchedRule=8b04d5e3775d298e78455efc5ca404d5',
    ),
    'rules' => 
    array (
      0 => 
      array (
        'hash' => '8b04d5e3775d298e78455efc5ca404d5',
        'prefixedQueryArray' => 
        array (
          'first' => 'first',
          'matchedRule' => '8b04d5e3775d298e78455efc5ca404d5',
        ),
        'query' => 'index.php?first=first&matchedRule=8b04d5e3775d298e78455efc5ca404d5',
        'queryArray' => 
        array (
          'first' => 'first',
          'matchedRule' => '8b04d5e3775d298e78455efc5ca404d5',
        ),
        'regex' => 'first',
      ),
    ),
    'handler' => 'firsthandler',
    'prefixedToUnprefixedQueryVariablesMap' => 
    array (
      'first' => 'first',
      'matchedRule' => 'matchedRule',
    ),
    'queryVariables' => 
    array (
      0 => 'first',
      1 => 'matchedRule',
    ),
    'isActiveCallback' => NULL,
  ),
  1 => 
  array (
    'methods' => 
    array (
      0 => 'POST',
    ),
    'rewriteRules' => 
    array (
      'second' => 'index.php?second=second&matchedRule=a9f0e61a137d86aa9db53465e0801612',
    ),
    'rules' => 
    array (
      0 => 
      array (
        'hash' => 'a9f0e61a137d86aa9db53465e0801612',
        'prefixedQueryArray' => 
        array (
          'second' => 'second',
          'matchedRule' => 'a9f0e61a137d86aa9db53465e0801612',
        ),
        'query' => 'index.php?second=second&matchedRule=a9f0e61a137d86aa9db53465e0801612',
        'queryArray' => 
        array (
          'second' => 'second',
          'matchedRule' => 'a9f0e61a137d86aa9db53465e0801612',
        ),
        'regex' => 'second',
      ),
    ),
    'handler' => 'secondhandler',
    'prefixedToUnprefixedQueryVariablesMap' => 
    array (
      'second' => 'second',
      'matchedRule' => 'matchedRule',
    ),
    'queryVariables' => 
    array (
      0 => 'second',
      1 => 'matchedRule',
    ),
    'isActiveCallback' => 'secondisactive',
  ),
);
