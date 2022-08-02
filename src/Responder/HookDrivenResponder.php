<?php

declare(strict_types=1);

namespace ToyWpRouting\Responder;

use RuntimeException;
use ToyWpRouting\Support;

class HookDrivenResponder implements ResponderInterface
{
    protected array $actions = [];
    protected array $conflictChecks = [];
    protected array $filters = [];

    public function respond(): void
    {
        $this->initializeTraits();
        $this->checkForConflicts();

        foreach ($this->actions as $tag => $actionList) {
            foreach ($actionList as [$callback, $priority]) {
                add_action($tag, $callback, $priority, 999);
            }
        }

        foreach ($this->filters as $tag => $filterList) {
            foreach ($filterList as [$callback, $priority]) {
                add_filter($tag, $callback, $priority, 999);
            }
        }
    }

    public function withAction(string $tag, callable $callback, int $priority = 10): self
    {
        $this->addAction($tag, $callback, $priority);

        return $this;
    }

    public function withActions(array $actions): self
    {
        $this->actions = [];

        foreach ($actions as $tag => $$actionList) {
            foreach ($actionList as [$callback, $priority]) {
                $this->addAction($tag, $callback, $priority);
            }
        }

        return $this;
    }

    public function withFilter(string $tag, callable $callback, int $priority = 10): self
    {
        $this->addFilter($tag, $callback, $priority);

        return $this;
    }

    public function withFilters(array $filters): self
    {
        $this->filters = [];

        foreach ($filters as $tag => $filterList) {
            foreach ($filterList as [$callback, $priority]) {
                $this->addFilter($tag, $callback, $priority);
            }
        }

        return $this;
    }

    protected function addAction(string $tag, callable $callback, int $priority = 10): void
    {
        if (! array_key_exists($tag, $this->actions)) {
            $this->actions[$tag] = [];
        }

        $this->actions[$tag][] = [$callback, $priority];
    }

    protected function addConflictCheck(callable $callback): void
    {
        $this->conflictChecks[] = $callback;
    }

    protected function addFilter(string $tag, callable $callback, int $priority = 10): void
    {
        if (! array_key_exists($tag, $this->filters)) {
            $this->filters[$tag] = [];
        }

        $this->filters[$tag][] = [$callback, $priority];
    }

    protected function checkForConflicts(): void
    {
        foreach ($this->conflictChecks as $callback) {
            if (is_string($message = $callback())) {
                throw new RuntimeException($message);
            }
        }
    }

    protected function initializeTraits(): void
    {
        foreach (Support::classUsesRecursive(static::class) as $trait) {
            $method = 'initialize' . Support::classBaseName($trait);

            if (method_exists($this, $method)) {
                $this->{$method}();
            }
        }
    }
}
