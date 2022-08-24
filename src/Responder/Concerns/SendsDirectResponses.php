<?php

declare(strict_types=1);

namespace ToyWpRouting\Responder\Concerns;

/**
 * @psalm-require-extends \ToyWpRouting\Responder\HookDrivenResponder
 */
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
            if (! $this->isSendingDirectResponse()) {
                return;
            }

            if (
                method_exists($this, 'isModifyingResponseHtml')
                && $this->isModifyingResponseHtml()
            ) {
                return 'Cannot send direct response and modify response HTML at the same time';
            }

            if (method_exists($this, 'isSendingJsonResponse') && $this->isSendingJsonResponse()) {
                return 'Cannot send direct response and JSON response at the same time';
            }

            if (
                method_exists($this, 'isSendingRedirectResponse')
                && $this->isSendingRedirectResponse()
            ) {
                return 'Cannot send direct response and redirect response at the same time';
            }
        });
    }

    protected function isSendingDirectResponse(): bool
    {
        return is_string($this->sendsDirectResponses['body']);
    }
}
