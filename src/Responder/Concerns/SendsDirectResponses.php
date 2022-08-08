<?php

declare(strict_types=1);

namespace ToyWpRouting\Responder\Concerns;

trait SendsDirectResponses
{
    protected array $sendsDirectResponses = [
        'body' => null,
    ];

    public function withBody(string $body): self
    {
        $this->sendsDirectResponses['body'] = $body;

        return $this;
    }

    protected function initializeSendsDirectResponses(): void
    {
        $this->addFilter('template_include', function ($template) {
            if (! is_string($this->sendsDirectResponses['body'])) {
                return $template;
            }

            echo $this->sendsDirectResponses['body'];

            return dirname(__DIR__, 3) . '/templates/blank.php';
        });

        $this->addConflictCheck(function () {
            // @todo json? redirect?
            if (! is_string($this->sendsDirectResponses['body'])) {
                return;
            }

            if (
                method_exists($this, 'isModifyingResponseHtmlTemplate')
                && $this->isModifyingResponseHtmlTemplate()
            ) {
                return 'Cannot set both response body and template';
            }
        });
    }
}
