<?php

declare(strict_types=1);

namespace ToyWpRouting;

interface InvocationStrategyInterface
{
    public function getContext(): array;
    public function invokeHandler(RewriteInterface $rewrite);
    public function invokeIsActiveCallback(RewriteInterface $rewrite);
    public function withAdditionalContext(array $context): InvocationStrategyInterface;
    public function withContext(array $context): InvocationStrategyInterface;
}
