<?php

declare(strict_types=1);

namespace ToyWpRouting\Support;

use ToyWpRouting\CallableResolver\CallableResolverInterface;
use ToyWpRouting\Exception\HttpExceptionInterface;
use ToyWpRouting\Exception\MethodNotAllowedHttpException;
use ToyWpRouting\Exception\RewriteDisabledException;
use ToyWpRouting\Exception\RewriteInvocationExceptionInterface;
use ToyWpRouting\InvocationStrategy\InvocationStrategyInterface;
use ToyWpRouting\Responder\HierarchicalResponderInterface;
use ToyWpRouting\Responder\HttpExceptionResponder;
use ToyWpRouting\Responder\ResponderInterface;

class Orchestrator
{
    protected CallableResolverInterface $callableResolver;

    protected InvocationStrategyInterface $invocationStrategy;

    protected RequestContext $requestContext;

    protected RewriteCollection $rewriteCollection;

    public function __construct(
        RewriteCollection $rewriteCollection,
        InvocationStrategyInterface $invocationStrategy,
        CallableResolverInterface $callableResolver,
        RequestContext $requestContext
    ) {
        $this->callableResolver = $callableResolver;
        $this->invocationStrategy = $invocationStrategy;
        $this->requestContext = $requestContext;
        $this->rewriteCollection = $rewriteCollection;
    }

    public function initialize(): self
    {
        add_action('parse_request', [$this, 'onParseRequest'], -99);

        add_filter('option_rewrite_rules', [$this, 'onOptionRewriteRules'], 99);
        add_filter('rewrite_rules_array', [$this, 'onRewriteRulesArray'], 99);
        add_filter('pre_update_option_rewrite_rules', [$this, 'onPreUpdateOptionRewriteRules'], -99);
        add_filter('query_vars', [$this, 'onQueryVars'], 99);

        return $this;
    }

    /**
     * @template T
     *
     * @psalm-param T $rules
     *
     * @psalm-return T|array
     */
    public function onOptionRewriteRules($rules)
    {
        if (! $this->shouldModifyRules($rules)) {
            return $rules;
        }

        return $this->mergeActiveRewriteRules($rules);
    }

    /**
     * @param \WP|mixed $wp
     */
    public function onParseRequest($wp): void
    {
        if (
            is_object($wp)
            && property_exists($wp, 'matched_rule')
            && is_string($wp->matched_rule)
            && property_exists($wp, 'query_vars')
            && is_array($wp->query_vars)
        ) {
            /** @var object{matched_rule: string, query_vars: array} $wp */
            $this->respondToMatchedRegex($wp);
        }
    }

    /**
     * @template T
     *
     * @psalm-param T $rules
     *
     * @psalm-return T|array
     */
    public function onPreUpdateOptionRewriteRules($rules)
    {
        if (! $this->shouldModifyRules($rules)) {
            return $rules;
        }

        return array_diff_key($rules, $this->rewriteCollection->getRewriteRules());
    }

    /**
     * @template T
     *
     * @psalm-param T $vars
     *
     * @psalm-return T|array
     */
    public function onQueryVars($vars)
    {
        if (! is_array($vars)) {
            return $vars;
        }

        return array_merge($this->rewriteCollection->getQueryVariables(), $vars);
    }

    /**
     * @template T
     *
     * @psalm-param T $rules
     *
     * @psalm-return T|array
     */
    public function onRewriteRulesArray($rules)
    {
        if (! $this->shouldModifyRules($rules)) {
            return $rules;
        }

        return $this->mergeActiveRewriteRules($rules);
    }

    /**
     * @return mixed
     */
    protected function callHandler(Rewrite $rewrite, array $queryVariables)
    {
        return $this->invocationStrategy->invoke(
            $this->callableResolver->resolve($rewrite->getHandler()),
            $rewrite->getConcernedQueryVariablesWithoutPrefix($queryVariables)
        );
    }

    protected function isRewriteActive(Rewrite $rewrite): bool
    {
        $callback = $this->callableResolver->resolve($rewrite->getIsActiveCallback());

        return (bool) $this->invocationStrategy->invoke($callback);
    }

    protected function mergeActiveRewriteRules(array $rules): array
    {
        return array_merge($this->rewriteCollection->getRewriteRules(), $rules);
    }

    /**
     * @psalm-param object{matched_rule: string, query_vars: array} $wp
     */
    protected function respondToMatchedRegex($wp): void
    {
        $rewrites = $this->rewriteCollection->findByRegex($wp->matched_rule);

        if ([] === $rewrites) {
            return;
        }

        try {
            $method = $this->requestContext->getIntendedMethod();

            if (! array_key_exists($method, $rewrites)) {
                throw new MethodNotAllowedHttpException(array_keys($rewrites));
            }

            $rewrite = $rewrites[$method];

            if (! $this->isRewriteActive($rewrite)) {
                throw new RewriteDisabledException();
            }

            $responder = $this->callHandler($rewrite, $wp->query_vars);
        } catch (HttpExceptionInterface $e) {
            $responder = new HttpExceptionResponder($e);
        } catch (RewriteInvocationExceptionInterface $e) {
            $responder = new HttpExceptionResponder($e->toHttpException());
        }

        while (
            $responder instanceof HierarchicalResponderInterface
            && $responder->getParent() instanceof ResponderInterface
        ) {
            $responder = $responder->getParent();
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