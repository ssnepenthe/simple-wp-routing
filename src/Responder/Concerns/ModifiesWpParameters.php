<?php

declare(strict_types=1);

namespace ToyWpRouting\Responder\Concerns;

use WP;

trait ModifiesWpParameters
{
    protected array $modifiesWpParametersData = [
        'queryVariables' => [],
        'overwrite' => false,
    ];

    public function withAdditionalPreLoopQueryVariables(array $queryVariables): self
    {
        foreach ($queryVariables as $key => $value) {
            $this->withPreLoopQueryVariable($key, $value);
        }

        return $this;
    }

    public function withExistingPreLoopQueryVariablesOverwritten(): self
    {
        $this->modifiesWpParametersData['overwrite'] = true;

        return $this;
    }

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
            if (empty($this->modifiesWpParametersData['queryVariables'])) {
                return;
            }

            if ($this->modifiesWpParametersData['overwrite']) {
                $wp->query_vars = $this->modifiesWpParametersData['queryVariables'];
            } else {
                foreach ($this->modifiesWpParametersData['queryVariables'] as $key => $value) {
                    $wp->set_query_var($key, $value);
                }
            }
        });
    }
}
