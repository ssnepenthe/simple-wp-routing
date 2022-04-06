<?php

declare(strict_types=1);

namespace ToyWpRouting;

interface RewriteInterface
{
    public function getHandler();
    public function getIsActiveCallback();
    public function getMethods(): array;
    public function getPrefixedToUnprefixedQueryVariablesMap(): array;
    public function getQueryVariables(): array;
    public function getRules(): array;
}
