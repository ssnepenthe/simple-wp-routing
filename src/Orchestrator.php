<?php

declare(strict_types=1);

namespace ToyWpRouting;

use ToyWpRouting\Exception\HttpExceptionInterface;
use ToyWpRouting\Exception\MethodNotAllowedHttpException;
use ToyWpRouting\Exception\RewriteDisabledException;
use ToyWpRouting\Exception\RewriteInvocationExceptionInterface;
use ToyWpRouting\Responder\HierarchicalResponderInterface;
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

    public function onParseRequest($wp)
    {
        $this->respondToMatchedRegex($wp);
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

    protected function mergeActiveRewriteRules(array $rules): array
    {
        return array_merge($this->rewriteCollection->getRewriteRules(), $rules);
    }

    protected function respondToMatchedRegex($wp): void
    {
        $rewrites = $this->rewriteCollection->findByRegex($wp->matched_rule);

        if ([] === $rewrites) {
            return;
        }

        try {
            $method = $this->getRequestContext()->getIntendedMethod();

            if (! array_key_exists($method, $rewrites)) {
                throw new MethodNotAllowedHttpException(array_keys($rewrites));
            }

            $rewrite = $rewrites[$method];

            if (! $rewrite->isActive()) {
                throw new RewriteDisabledException();
            }

            $validated = $rewrite->validate($wp->query_vars);

            $responder = $rewrite->handle($validated);
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
