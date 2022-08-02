<?php

declare(strict_types=1);

namespace ToyWpRouting\Responder;

use InvalidArgumentException;
use WP_Query;

trait ModifiesWpQueryParameters
{
    protected array $modifiesWpQueryParametersData = [
        'flags' => [],
        'flagsInitialState' => [
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
        ],
        'queryVariables' => [],
    ];

    public function withAllQueryFlagsReset(): self
    {
        $this->modifiesWpQueryParametersData['flags'] = $this->modifiesWpQueryParametersData['flagsInitialState'];

        return $this;
    }

    public function withQueryFlag(string $key, bool $value): self
    {
        if (! array_key_exists($key, $this->modifiesWpQueryParametersData['flagsInitialState'])) {
            throw new InvalidArgumentException('@todo');
        }

        $this->modifiesWpQueryParametersData['flags'][$key] = $value;

        return $this;
    }

    public function withQueryFlags(array $queryFlags): self
    {
        $this->modifiesWpQueryParametersData['flags'] = [];

        foreach ($queryFlags as $key => $value) {
            $this->withQueryFlag($key, $value);
        }

        return $this;
    }

    public function withQueryVariable(string $key, $value): self
    {
        $this->modifiesWpQueryParametersData['queryVariables'][$key] = $value;

        return $this;
    }

    public function withQueryVariables(array $queryVariables): self
    {
        $this->modifiesWpQueryParametersData['queryVariables'] = [];

        foreach ($queryVariables as $key => $value) {
            $this->withQueryVariable($key, $value);
        }

        return $this;
    }

    protected function initializeModifiesWpQueryParameters(): void
    {
        $this->addAction('parse_query', function (WP_Query $wpQuery) {
            foreach ($this->modifiesWpQueryParametersData['flags'] as $key => $value) {
                $wpQuery->{$key} = $value;
            }

            foreach ($this->modifiesWpQueryParametersData['queryVariables'] as $key => $value) {
                $wpQuery->query_vars[$key] = $value;
            }
        });
    }
}
