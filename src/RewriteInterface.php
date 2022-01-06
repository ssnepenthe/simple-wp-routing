<?php

namespace ToyWpRouting;

use Invoker\InvokerInterface;

interface RewriteInterface
{
    public function getHandler();
    public function getIsActiveCallback();
    public function getMethod(): string;
    public function getPrefixedToUnprefixedQueryVariablesMap(): array;
    public function getQuery(): string;
    public function getQueryVariables(): array;
    public function getRegex(): string;
    public function isActive(?InvokerInterface $invoker = null): bool;
    public function setIsActiveCallback($isActiveCallback);
}
