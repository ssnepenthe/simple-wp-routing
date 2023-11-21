<?php

declare(strict_types=1);

namespace SimpleWpRouting\Support;

use SimpleWpRouting\CallableResolver\CallableResolverInterface;
use SimpleWpRouting\Exception\HttpExceptionInterface;
use SimpleWpRouting\Exception\MethodNotAllowedHttpException;
use SimpleWpRouting\Exception\RewriteDisabledException;
use SimpleWpRouting\Exception\RewriteInvocationExceptionInterface;
use SimpleWpRouting\Invoker\InvokerInterface;
use SimpleWpRouting\Responder\HierarchicalResponderInterface;
use SimpleWpRouting\Responder\HttpExceptionResponder;
use SimpleWpRouting\Responder\ResponderInterface;

final class Orchestrator
{
    private CallableResolverInterface $callableResolver;

    private InvokerInterface $invocationStrategy;

    private RequestContext $requestContext;

    private RewriteCollection $rewriteCollection;

    public function __construct(
        RewriteCollection $rewriteCollection,
        InvokerInterface $invocationStrategy,
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
     * @param array|mixed $rules
     *
     * @return array|mixed
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
     * @param array|mixed $rules
     *
     * @return array|mixed
     */
    public function onPreUpdateOptionRewriteRules($rules)
    {
        if (! $this->shouldModifyRules($rules)) {
            return $rules;
        }

        return array_diff_key($rules, $this->rewriteCollection->getRewriteRules());
    }

    /**
     * @param array|mixed $vars
     *
     * @return array|mixed
     */
    public function onQueryVars($vars)
    {
        if (! is_array($vars)) {
            return $vars;
        }

        return array_merge($this->rewriteCollection->getQueryVariables(), $vars);
    }

    /**
     * @param array|mixed $rules
     *
     * @return array|mixed
     */
    public function onRewriteRulesArray($rules)
    {
        if (! $this->shouldModifyRules($rules)) {
            return $rules;
        }

        return $this->mergeActiveRewriteRules($rules);
    }

    /**
     * @param array<string, string> $queryVariables
     *
     * @return mixed
     */
    private function callHandler(Rewrite $rewrite, array $queryVariables)
    {
        return $this->invocationStrategy->invoke(
            $this->callableResolver->resolve($rewrite->getHandler()),
            $rewrite->getConcernedQueryVariablesWithoutPrefix($queryVariables)
        );
    }

    private function isRewriteActive(Rewrite $rewrite): bool
    {
        $callback = $this->callableResolver->resolve($rewrite->getIsActiveCallback());

        return (bool) $this->invocationStrategy->invoke($callback);
    }

    /**
     * @param array<string, string> $rules
     *
     * @return array<string, string>
     */
    private function mergeActiveRewriteRules(array $rules): array
    {
        return array_merge($this->rewriteCollection->getRewriteRules(), $rules);
    }

    /**
     * @param object{matched_rule: string, query_vars: array} $wp
     */
    private function respondToMatchedRegex($wp): void
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
    private function shouldModifyRules($rules): bool
    {
        return is_array($rules) && count($rules) > 0;
    }
}
