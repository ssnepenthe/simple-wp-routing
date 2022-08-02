<?php

declare(strict_types=1);

namespace ToyWpRouting\Responder;

use WP;

trait ModifiesWpParameters
{
    protected array $modifiesWpParametersData = [
        'queryVariables' => [],
    ];

    public function withPreLoopQueryVariable(string $key, $value): self
    {
        $this->modifiesWpParametersData['queryVariables'][$key] = $value;

        return $this;
    }

    public function withPreLoopQueryVariables(array $queryVariables): self
    {
        $this->modifiesWpParametersData['queryVariables'] = [];

        foreach ($queryVariables as $key => $value) {
            $this->withPreLoopQueryVariable($key, $value);
        }

        return $this;
    }

    protected function initializeModifiesWpParameters(): void
    {
        $this->addAction('parse_request', function (WP $wp) {
            foreach ($this->modifiesWpParametersData['queryVariables'] as $key => $value) {
                $wp->set_query_var($key, $value);
            }
        });
    }
}
