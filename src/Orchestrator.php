<?php

namespace ToyWpRouting;

class Orchestrator
{
    protected $container;

    public function __construct(string $cacheDir)
    {
        $this->container = new Container();
        $this->container->setCacheDir($cacheDir);
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
        if (! $this->container->getRewriteCollectionLoader()->hasCachedRewrites()) {
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

    public function cacheRewrites()
    {
        $this->container->getRewriteCollectionDumper()->toFile(
            $this->container->getCacheDir(),
            $this->container->getCacheFile()
        );
    }

    protected function mergeActiveRewriteRules($rules)
    {
        return array_merge(
            $this->container->getActiveRewriteCollection()->getRewriteRules(),
            $rules
        );
    }

    protected function resolveRewriteParameters(RewriteInterface $rewrite, array $queryVars)
    {
        $resolved = [];

        // @todo also in snake case?
        // @todo Test with optional params.
        foreach ($rewrite->getPrefixedToUnprefixedQueryVariablesMap() as $prefixed => $unprefixed) {
            if (array_key_exists($prefixed, $queryVars)) {
                $resolved[$unprefixed] = $queryVars[$prefixed];
            }
        }

        return $resolved;
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
            $matchedRewrite = $candidates[$method];

            $responder = $this->container->getInvoker()->call(
                $matchedRewrite->getHandler(),
                $this->resolveRewriteParameters($matchedRewrite, $queryVars)
            );
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
