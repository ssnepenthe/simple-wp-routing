<?php

namespace ToyWpRouting;

interface InvocationStrategyInterface
{
	public function invokeHandler(RewriteInterface $rewrite);
	public function invokeIsActiveCallback(RewriteInterface $rewrite);
	public function withAdditionalContext(array $context);
}
