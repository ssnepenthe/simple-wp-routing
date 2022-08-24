<?php

declare(strict_types=1);

namespace ToyWpRouting\Responder\Concerns;

use InvalidArgumentException;

/**
 * @psalm-require-extends \ToyWpRouting\Responder\HookDrivenResponder
 */
trait SendsJsonResponses
{
    protected array $sendsJsonResponsesData = [
        'hasData' => false,
        'data' => null,
        'options' => 0,
        'status' => 200,
    ];

    /**
     * @param mixed $data
     */
    public function withJsonData($data): self
    {
        $this->sendsJsonResponsesData['hasData'] = true;
        $this->sendsJsonResponsesData['data'] = $data;

        return $this;
    }

    public function withJsonOptions(int $options): self
    {
        $this->sendsJsonResponsesData['options'] = $options;

        return $this;
    }

    public function withJsonStatusCode(int $statusCode): self
    {
        // 1xx responses shouldn't have a body - should we allow them here anyway?
        if ($statusCode < 200 || ($statusCode >= 300 && $statusCode < 400) || $statusCode >= 600) {
            throw new InvalidArgumentException(
                'JSON response code must be between 200 and 299 or between 400 and 599'
            );
        }

        $this->sendsJsonResponsesData['status'] = $statusCode;

        return $this;
    }

    protected function initializeSendsJsonResponses(): void
    {
        $this->addAction('template_redirect', function () {
            if (! $this->isSendingJsonResponse()) {
                return;
            }

            if (
                $this->sendsJsonResponsesData['status'] >= 200
                && $this->sendsJsonResponsesData['status'] < 300
            ) {
                wp_send_json_success(
                    $this->sendsJsonResponsesData['data'],
                    $this->sendsJsonResponsesData['status'],
                    $this->sendsJsonResponsesData['options']
                );
            } else {
                wp_send_json_error(
                    $this->sendsJsonResponsesData['data'],
                    $this->sendsJsonResponsesData['status'],
                    $this->sendsJsonResponsesData['options']
                );
            }
        });

        $this->addConflictCheck(function () {
            if (! $this->isSendingJsonResponse()) {
                return;
            }

            if (
                method_exists($this, 'isModifyingResponseStatus')
                && $this->isModifyingResponseStatus()
            ) {
                return 'Cannot set status code on JSON response via "withStatusCode" method'
                    . ' - must use "withJsonStatusCode" method';
            }

            if (
                method_exists($this, 'isModifyingResponseHtml')
                && $this->isModifyingResponseHtml()
            ) {
                return 'Cannot send JSON response and modify response HTML at the same time';
            }

            if (
                method_exists($this, 'isSendingDirectResponse')
                && $this->isSendingDirectResponse()
            ) {
                return 'Cannot send JSON response and direct response at the same time';
            }

            if (
                method_exists($this, 'isSendingRedirectResponse')
                && $this->isSendingRedirectResponse()
            ) {
                return 'Cannot send JSON response and redirect response at the same time';
            }
        });
    }

    protected function isSendingJsonResponse(): bool
    {
        return $this->sendsJsonResponsesData['hasData'];
    }
}
