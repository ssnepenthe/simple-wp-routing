<?php

declare(strict_types=1);

namespace ToyWpRouting\Responder\Partial;

use InvalidArgumentException;
use WP_Query;

final class WpQueryPartial implements PartialInterface
{
    use PartialTrait;

    private array $defaultFlags = [
        'is_single' => false,
        'is_preview' => false,
        'is_page' => false,
        'is_archive' => false,
        'is_date' => false,
        'is_year' => false,
        'is_month' => false,
        'is_day' => false,
        'is_time' => false,
        'is_author' => false,
        'is_category' => false,
        'is_tag' => false,
        'is_tax' => false,
        'is_search' => false,
        'is_feed' => false,
        'is_comment_feed' => false,
        'is_trackback' => false,
        'is_home' => false,
        'is_privacy_policy' => false,
        'is_404' => false,
        'is_embed' => false,
        'is_paged' => false,
        'is_admin' => false,
        'is_attachment' => false,
        'is_singular' => false,
        'is_robots' => false,
        'is_favicon' => false,
        'is_posts_page' => false,
        'is_post_type_archive' => false,
    ];
    private array $flags = [];
    private bool $overwriteQueryVariables = false;
    private array $queryVariables = [];
    private bool $resetFlags = false;

    public function addFlag(string $flag, bool $value): self
    {
        if (! array_key_exists($flag, $this->defaultFlags)) {
            throw new InvalidArgumentException("Cannot set unrecognized query flag \"{$flag}\"");
        }

        $this->flags[$flag] = $value;

        return $this;
    }

    public function addFlags(array $flags): self
    {
        foreach ($flags as $flag => $value) {
            $this->addFlag($flag, $value);
        }

        return $this;
    }

    /**
     * @param mixed $value
     */
    public function addQueryVariable(string $key, $value): self
    {
        $this->queryVariables[$key] = $value;

        return $this;
    }

    public function addQueryVariables(array $queryVariables): self
    {
        foreach ($queryVariables as $key => $value) {
            $this->addQueryVariable($key, $value);
        }

        return $this;
    }

    public function dontOverwriteQueryVariables(): self
    {
        $this->overwriteQueryVariables = false;

        return $this;
    }

    public function dontResetFlags(): self
    {
        $this->resetFlags = false;

        return $this;
    }

    /**
     * @internal
     */
    public function onParseQuery(WP_Query $wpQuery): void
    {
        if (! $wpQuery->is_main_query() || (
            [] === $this->flags
            && [] === $this->queryVariables
            && false === $this->resetFlags
        )) {
            return;
        }

        $this->transferFlagsToWpQuery($wpQuery);
        $this->transferQueryVariablesToWpQuery($wpQuery);
    }

    public function overwriteQueryVariables(): self
    {
        $this->overwriteQueryVariables = true;

        return $this;
    }

    public function resetFlags(): self
    {
        $this->resetFlags = true;

        return $this;
    }

    /**
     * @internal
     */
    public function respond(): void
    {
        add_action('parse_query', [$this, 'onParseQuery']);
    }

    public function setFlags(array $flags): self
    {
        $this->flags = [];

        foreach ($flags as $flag => $value) {
            $this->addFlag($flag, $value);
        }

        return $this;
    }

    public function setQueryVariables(array $queryVariables): self
    {
        $this->queryVariables = [];

        foreach ($queryVariables as $key => $value) {
            $this->addQueryVariable($key, $value);
        }

        return $this;
    }

    private function transferFlagsToWpQuery(WP_Query $wpQuery): void
    {
        if ($this->resetFlags) {
            foreach ($this->defaultFlags as $flag => $value) {
                $wpQuery->{$flag} = $value;
            }
        }

        foreach ($this->flags as $flag => $value) {
            $wpQuery->{$flag} = $value;
        }
    }

    private function transferQueryVariablesToWpQuery(WP_Query $wpQuery): void
    {
        if ($this->overwriteQueryVariables) {
            $wpQuery->query_vars = $this->queryVariables;
        } else {
            foreach ($this->queryVariables as $key => $value) {
                $wpQuery->query_vars[$key] = $value;
            }
        }
    }
}
