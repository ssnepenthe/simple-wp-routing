<?php

declare(strict_types=1);

namespace ToyWpRouting;

use RuntimeException;

class Orchestrator
{
    protected $container;

    public function __construct()
    {
        $this->container = new Container();
    }

    public function cacheRewrites()
    {
        if ($this->container->getRewriteCollectionCache()->exists()) {
            throw new RuntimeException('@todo');
        }

        $this->container->getRewriteCollectionCache()->put(
            $this->container->getRewriteCollection()
        );
    }

    public function getContainer()
    {
        return $this->container;
    }

    public function initialize()
    {
        // @todo adjust priorities.
        add_action('init', [$this, 'onInit']);
        add_filter('option_rewrite_rules', [$this, 'onOptionRewriteRules']);
        add_filter('rewrite_rules_array', [$this, 'onRewriteRulesArray']);
        add_filter('pre_update_option_rewrite_rules', [$this, 'onPreUpdateOptionRewriteRules']);
        add_filter('query_vars', [$this, 'onQueryVars']);
        add_filter('request', [$this, 'onRequest']);

        return $this;
    }

    public function onInit()
    {
        if (! $this->rewriteCacheIsConfigured() || ! $this->rewritesAreCached()) {
            do_action('toy_wp_routing.init', $this->container->getRouteCollection());
        }
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

        return array_diff_key($rules, $this->container->getRewriteCollection()->getRewriteRules());
    }

    public function onQueryVars($vars)
    {
        if (! is_array($vars)) {
            return $vars;
        }

        // @todo only active rewrites?
        return array_merge($this->container->getRewriteCollection()->getQueryVariables(), $vars);
    }

    public function onRequest($queryVars)
    {
        $this->respondToMatchedRouteHash($queryVars);

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
        return array_merge(
            $this->container->getActiveRewriteCollection()->getRewriteRules(),
            $rules
        );
    }

    protected function respondToMatchedRouteHash($queryVars)
    {
        // @todo Make this more testable...
        $qv = $this->container->getPrefix() . 'matchedRoute';

        if (
            ! is_array($queryVars)
            || ! array_key_exists($qv, $queryVars)
            || ! is_string($matchedRouteHash = $queryVars[$qv])
        ) {
            return;
        }

        // @todo active rewrite collection?
        $candidates = $this->container
            ->getRewriteCollection()
            ->getRewritesByRegexHash($matchedRouteHash);

        if (empty($candidates)) {
            return;
        }

        $method = $this->container->getRequestContext()->getIntendedMethod();

        if (! array_key_exists($method, $candidates)) {
            $responder = new MethodNotAllowedResponder(array_keys($candidates));
        } else {
            $responder = $this->container
                ->getInvocationStrategy()
                ->withAdditionalContext(compact('queryVars'))
                ->invokeHandler($candidates[$method]);
        }

        if ($responder instanceof ResponderInterface) {
            $responder->respond();
        }
    }

    protected function rewriteCacheIsConfigured(): bool
    {
        return $this->container->cacheDirIsSet();
    }

    protected function rewritesAreCached(): bool
    {
        return $this->container->getRewriteCollectionCache()->exists();
    }

    protected function shouldModifyRules($rules): bool
    {
        return is_array($rules) && count($rules) > 0;
    }
}
