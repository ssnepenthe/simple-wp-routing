<?php

declare(strict_types=1);

namespace ToyWpRouting;

class Orchestrator
{
    protected $activeRewriteCollection;
    protected $invocationStrategy;
    protected $requestContext;
    protected $rewriteCollection;

    public function __construct(
        RewriteCollection $rewriteCollection,
        ?InvocationStrategyInterface $invocationStrategy = null,
        ?RequestContext $requestContext = null
    ) {
        $this->invocationStrategy = $invocationStrategy ?: new DefaultInvocationStrategy();
        $this->requestContext = $requestContext;
        $this->rewriteCollection = $rewriteCollection;
    }

    public function getActiveRewriteCollection(): RewriteCollection
    {
        if (! $this->activeRewriteCollection instanceof RewriteCollection) {
            $this->activeRewriteCollection = $this->rewriteCollection->filter(
                [$this->invocationStrategy, 'invokeIsActiveCallback']
            );
        }

        return $this->activeRewriteCollection;
    }

    public function getInvocationStrategy(): InvocationStrategyInterface
    {
        return $this->invocationStrategy;
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

    public function initialize()
    {
        // @todo adjust priorities.

        /**
         * @psalm-suppress HookNotFound
         */
        add_filter('option_rewrite_rules', [$this, 'onOptionRewriteRules']);
        add_filter('rewrite_rules_array', [$this, 'onRewriteRulesArray']);
        /**
         * @psalm-suppress HookNotFound
         */
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

        // @todo only active rewrites?
        return array_merge($this->rewriteCollection->getQueryVariables(), $vars);
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


    protected function mergeActiveRewriteRules($rules)
    {
        return array_merge($this->getActiveRewriteCollection()->getRewriteRules(), $rules);
    }

    protected function respondToMatchedRuleHash($queryVars)
    {
        if (! is_array($queryVars)) {
            return;
        }

        $hasMatchedRule = false;
        $matchedRuleKey = null;

        foreach ($queryVars as $key => $_) {
            if ('matchedRule' === substr($key, -11)) {
                $hasMatchedRule = true;
                $matchedRuleKey = $key;
                break;
            }
        }

        if (! $hasMatchedRule) {
            return;
        }

        /**
         * @psalm-suppress PossiblyNullArrayOffset
         * @todo The logic above for determining matched rule key can be refactored now that we have
         *       introduced the RewriteCollection->getPrefix() method. Or better yet, we should
         *       probably refactor to use $wp->matched_rule instead of a query variable.
         */
        if (! is_string($queryVars[$matchedRuleKey])) {
            return;
        }

        // @todo active rewrite collection?
        $candidates = $this->rewriteCollection
            ->getRewritesByRegexHash($queryVars[$matchedRuleKey]);

        if (empty($candidates)) {
            return;
        }

        $method = $this->getRequestContext()->getIntendedMethod();

        if (! array_key_exists($method, $candidates)) {
            $responder = new MethodNotAllowedResponder(array_keys($candidates));
        } else {
            $responder = $this->invocationStrategy
                ->withAdditionalContext(compact('queryVars'))
                ->invokeHandler($candidates[$method]);
        }

        if ($responder instanceof ResponderInterface) {
            $responder->respond();
        }
    }

    protected function shouldModifyRules($rules): bool
    {
        return is_array($rules) && count($rules) > 0;
    }
}
