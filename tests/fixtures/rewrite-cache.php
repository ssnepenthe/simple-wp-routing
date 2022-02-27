<?php

return array(
  0 =>
  array(
    'methods' =>
    array(
      0 => 'GET',
    ),
    'rules' =>
    array(
      '/first/' => 'index.php?first=first',
    ),
    'handler' => 'firsthandler',
    'prefixedToUnprefixedQueryVariablesMap' =>
    array(
      'first' => 'first',
    ),
    'queryVariables' =>
    array(
      0 => 'first',
    ),
    'isActiveCallback' => null,
  ),
  1 =>
  array(
    'methods' =>
    array(
      0 => 'POST',
    ),
    'rules' =>
    array(
      '/second/' => 'index.php?second=second',
    ),
    'handler' => 'secondhandler',
    'prefixedToUnprefixedQueryVariablesMap' =>
    array(
      'second' => 'second',
    ),
    'queryVariables' =>
    array(
      0 => 'second',
    ),
    'isActiveCallback' => 'secondisactive',
  ),
);
