<?php

namespace ToyWpRouting;

use InvalidArgumentException;

class DefaultInvocationStrategy implements InvocationStrategyInterface
{
	protected $context = [];

	public function invokeHandler(RewriteInterface $rewrite)
	{
		return ($rewrite->getHandler())($this->resolveAdditionalParameters($rewrite));
	}

	public function invokeIsActiveCallback(RewriteInterface $rewrite)
	{
		$callback = $rewrite->getIsActiveCallback();

		if (null === $callback) {
			return true;
		}

		if (! is_callable($callback)) {
			throw new InvalidArgumentException('@todo');
		}

		return (bool) $callback();
	}

	public function withAdditionalContext(array $context)
	{
		$this->context = array_merge($this->context, $context);

		return $this;
	}

	protected function resolveAdditionalParameters(RewriteInterface $rewrite)
    {
		if (! array_key_exists('queryVars', $this->context)) {
			return [];
		}

        $resolved = [];

        // @todo also in snake case?
        // @todo Test with optional params.
		// @todo Include all query vars?
		// @todo Include prefixed query vars as well?
        foreach ($rewrite->getPrefixedToUnprefixedQueryVariablesMap() as $prefixed => $unprefixed) {
            if (array_key_exists($prefixed, $this->context['queryVars'])) {
                $resolved[$unprefixed] = $this->context['queryVars'][$prefixed];
            }
        }

        return $resolved;
    }
}
