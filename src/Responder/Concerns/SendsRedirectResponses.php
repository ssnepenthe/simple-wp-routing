<?php

declare(strict_types=1);

namespace ToyWpRouting\Responder\Concerns;

use InvalidArgumentException;

// @todo Conflicts - headers contain x-redirect-by
trait SendsRedirectResponses
{
    protected array $sendsRedirectResponsesData = [
        'location' => null,
        'redirectBy' => 'WordPress',
        'safe' => true,
        'status' => 302,
    ];

    public function withRedirectByHeader(string $redirectBy): self
    {
        $this->sendsRedirectResponsesData['redirectBy'] = $redirectBy;

        return $this;
    }

    public function withRedirectLocation(string $location): self
    {
        $this->sendsRedirectResponsesData['location'] = $location;

        return $this;
    }

    public function withRedirectStatusCode(int $statusCode): self
    {
        if (! ($statusCode >= 300 && $statusCode < 400)) {
            throw new InvalidArgumentException('Redirect status code must be between 300 and 399');
        }

        $this->sendsRedirectResponsesData['status'] = $statusCode;

        return $this;
    }

    public function withUnsafeRedirectsAllowed(): self
    {
        $this->sendsRedirectResponsesData['safe'] = false;

        return $this;
    }

    protected function initializeSendsRedirectResponses()
    {
        $this->addAction('template_redirect', function () {
            if (! $this->isSendingRedirectResponse()) {
                return;
            }

            if ($this->sendsRedirectResponsesData['safe']) {
                $cancelled = wp_safe_redirect(
                    $this->sendsRedirectResponsesData['location'],
                    $this->sendsRedirectResponsesData['status'],
                    $this->sendsRedirectResponsesData['redirectBy']
                );
            } else {
                $cancelled = wp_redirect(
                    $this->sendsRedirectResponsesData['location'],
                    $this->sendsRedirectResponsesData['status'],
                    $this->sendsRedirectResponsesData['redirectBy']
                );
            }

            if (! $cancelled) {
                exit;
            }
        });

        $this->addConflictCheck(function () {
            if (! $this->isSendingRedirectResponse()) {
                return;
            }

            if (
                method_exists($this, 'isModifyingResponseStatus')
                && $this->isModifyingResponseStatus()
            ) {
                return 'Cannot set status code on redirect response via "withStatusCode" method'
                    . ' - must use "withRedirectStatusCode" method';
            }

            if (
                method_exists($this, 'isModifyingResponseHtml')
                && $this->isModifyingResponseHtml()
            ) {
                return 'Cannot send redirect response and modify response HTML at the same time';
            }

            if (
                method_exists($this, 'isSendingDirectResponse')
                && $this->isSendingDirectResponse()
            ) {
                return 'Cannot send redirect response and direct response at the same time';
            }

            if (method_exists($this, 'isSendingJsonResponse') && $this->isSendingJsonResponse()) {
                return 'Cannot send redirect response and JSON response at the same time';
            }
        });
    }

    protected function isSendingRedirectResponse(): bool
    {
        return is_string($this->sendsRedirectResponsesData['location']);
    }
}
