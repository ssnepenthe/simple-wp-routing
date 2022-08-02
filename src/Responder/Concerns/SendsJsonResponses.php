<?php

declare(strict_types=1);

namespace ToyWpRouting\Responder;

use InvalidArgumentException;

// @todo Conflicts - status
trait SendsJsonResponses
{
    protected array $sendsJsonResponsesData = [
        'hasData' => false,
        'data' => null,
        'options' => 0,
        'status' => 200,
    ];

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
            throw new InvalidArgumentException('@todo');
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

            if ($this->statusCode >= 200 && $this->statusCode < 300) {
                wp_send_json_success($this->data, $this->statusCode, $this->options);
            } else {
                wp_send_json_error($this->data, $this->statusCode, $this->options);
            }
        });

        $this->addConflictCheck(function () {
            if (!$this->isSendingJsonResponse()) {
                return;
            }

            if (
                method_exists($this, 'isModifyingResponseHtmlTemplate')
                && $this->isModifyingResponseHtmlTemplate()
            ) {
                return '@todo';
            }

            if (
                method_exists($this, 'isSendingRedirectResponse')
                && $this->isSendingRedirectResponse()
            ) {
                return '@todo';
            }
        });
    }

    protected function isSendingJsonResponse(): bool
    {
        return $this->sendsJsonResponsesData['hasData'];
    }
}
