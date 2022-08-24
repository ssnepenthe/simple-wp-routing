<?php

declare(strict_types=1);

namespace ToyWpRouting;

use RuntimeException;
use ToyWpRouting\Exception\HttpExceptionInterface;
use ToyWpRouting\Responder\HttpExceptionResponder;
use ToyWpRouting\Responder\ResponderInterface;

class Orchestrator
{
    /**
     * @var ?RequestContext
     */
    protected $requestContext;

    protected RewriteCollection $rewriteCollection;

    public function __construct(
        RewriteCollection $rewriteCollection,
        ?RequestContext $requestContext = null
    ) {
        $this->requestContext = $requestContext;
        $this->rewriteCollection = $rewriteCollection;
    }

    public function getRequestContext(): RequestContext
    {
        if (null === $this->requestContext) {
            $this->requestContext = RequestContext::fromGlobals();
        }

        return $this->requestContext;
    }

    public function getRewriteCollection(): RewriteCollection
    {
        return $this->rewriteCollection;
    }

    public function initialize(): self
    {
        // @todo adjust priorities.
        add_filter('option_rewrite_rules', [$this, 'onOptionRewriteRules']);
        add_filter('rewrite_rules_array', [$this, 'onRewriteRulesArray']);
        add_filter('pre_update_option_rewrite_rules', [$this, 'onPreUpdateOptionRewriteRules']);
        add_filter('query_vars', [$this, 'onQueryVars']);
        add_filter('request', [$this, 'onRequest']);

        return $this;
    }

    public function onOptionRewriteRules($rules)
    {
        if (! $this->shouldModifyRules($rules)) {
            return $rules;
        }

        return $this->mergeActiveRewriteRules($rules);
    }

    public function onPreUpdateOptionRewriteRules($rules)
    {
        if (! $this->shouldModifyRules($rules)) {
            return $rules;
        }

        return array_diff_key($rules, $this->rewriteCollection->getRewriteRules());
    }

    public function onQueryVars($vars)
    {
        if (! is_array($vars)) {
            return $vars;
        }

        return array_merge($this->rewriteCollection->getActiveQueryVariables(), $vars);
    }

    public function onRequest($queryVars)
    {
        $this->respondToMatchedRuleHash($queryVars);

        return $queryVars;
    }

    public function onRewriteRulesArray($rules)
    {
        if (! $this->shouldModifyRules($rules)) {
            return $rules;
        }

        return $this->mergeActiveRewriteRules($rules);
    }

    protected function mergeActiveRewriteRules(array $rules): array
    {
        return array_merge($this->rewriteCollection->getActiveRewriteRules(), $rules);
    }

    /**
     * @param mixed $queryVars
     */
    protected function respondToMatchedRuleHash($queryVars): void
    {
        if (! is_array($queryVars)) {
            return;
        }

        $matchedRuleKey = "{$this->rewriteCollection->getPrefix()}matchedRule";

        if (
            ! array_key_exists($matchedRuleKey, $queryVars)
            || ! is_string($queryVars[$matchedRuleKey])
        ) {
            return;
        }

        try {
            $rewrite = $this->rewriteCollection->findActiveRewriteByHashAndMethod(
                $queryVars[$matchedRuleKey],
                $this->getRequestContext()->getIntendedMethod()
            );

            if (! $rewrite instanceof RewriteInterface) {
                return;
            }

            $responder = $rewrite->handle($queryVars);
        } catch (HttpExceptionInterface $e) {
            $responder = new HttpExceptionResponder($e);
        } catch (RuntimeException $e) {
            // Invalid method override
            return;
        }

        if ($responder instanceof ResponderInterface) {
            $responder->respond();
        }
    }

    /**
     * @param mixed $rules
     */
    protected function shouldModifyRules($rules): bool
    {
        return is_array($rules) && count($rules) > 0;
    }
}
