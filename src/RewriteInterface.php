<?php

namespace ToyWpRouting;

interface RewriteInterface
{
    public function getRules(): array;
    public function getMethods(): array;
    public function getHandler();
    public function getQueryVariables(): array;
    public function getPrefixedToUnprefixedQueryVariablesMap(): array;
    public function getIsActiveCallback();
}
