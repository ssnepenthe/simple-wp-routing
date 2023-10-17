<?php

declare(strict_types=1);

namespace ToyWpRouting\Responder\Partial;

use WP;

final class WpPartial implements PartialInterface
{
    use PartialTrait;

    private bool $overwriteQueryVariables = false;
    private array $queryVariables = [];

    public function dontOverwriteQueryVariables(): self
    {
        $this->overwriteQueryVariables = false;

        return $this;
    }

    /**
     * @internal
     */
    public function onParseRequest(WP $wp): void
    {
        if ([] === $this->queryVariables) {
            return;
        }

        if ($this->overwriteQueryVariables) {
            $wp->query_vars = $this->queryVariables;
        } else {
            foreach ($this->queryVariables as $key => $value) {
                $wp->set_query_var($key, $value);
            }
        }
    }

    public function overwriteQueryVariables(): self
    {
        $this->overwriteQueryVariables = true;

        return $this;
    }

    /**
     * @internal
     */
    public function respond(): void
    {
        add_action('parse_request', [$this, 'onParseRequest']);
    }

    /**
     * @param mixed $value
     */
    public function setQueryVariable(string $key, $value): self
    {
        $this->queryVariables[$key] = $value;

        return $this;
    }

    public function setQueryVariables(array $queryVariables): self
    {
        foreach ($queryVariables as $key => $value) {
            $this->setQueryVariable($key, $value);
        }

        return $this;
    }
}
