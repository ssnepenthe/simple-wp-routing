<?php

declare(strict_types=1);

namespace ToyWpRouting\Responder\Concerns;

use WP;

/**
 * @psalm-require-extends \ToyWpRouting\Responder\HookDrivenResponder
 */
trait ModifiesWpParameters
{
    protected array $modifiesWpParametersData = [
        'queryVariables' => [],
        'overwrite' => false,
    ];

    public function withAdditionalRequestVariables(array $queryVariables): self
    {
        foreach ($queryVariables as $key => $value) {
            $this->withRequestVariable($key, $value);
        }

        return $this;
    }

    public function withExistingRequestVariablesOverwritten(): self
    {
        $this->modifiesWpParametersData['overwrite'] = true;

        return $this;
    }

    /**
     * @param mixed $value
     */
    public function withRequestVariable(string $key, $value): self
    {
        $this->modifiesWpParametersData['queryVariables'][$key] = $value;

        return $this;
    }

    public function withRequestVariables(array $queryVariables): self
    {
        $this->modifiesWpParametersData['queryVariables'] = [];

        foreach ($queryVariables as $key => $value) {
            $this->withRequestVariable($key, $value);
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
