<?php

declare(strict_types=1);

namespace ToyWpRouting\Responder\Concerns;

use InvalidArgumentException;

trait ModifiesResponseHeaders
{
    protected array $modifiesResponseHeadersData = [
        'headers' => [],
        'status' => null,
    ];

    public function withHeader(string $key, $values, bool $replace = true): self
    {
        if (is_array($values)) {
            $values = array_values($values);

            if (true === $replace || ! isset($this->modifiesResponseHeadersData['headers'][$key])) {
                $this->modifiesResponseHeadersData['headers'][$key] = $values;
            } else {
                $this->modifiesResponseHeadersData['headers'][$key] = array_merge(
                    $this->modifiesResponseHeadersData['headers'][$key],
                    $values
                );
            }
        } else {
            if (true === $replace || ! isset($this->modifiesResponseHeadersData['headers'][$key])) {
                $this->modifiesResponseHeadersData['headers'][$key] = [$values];
            } else {
                $this->modifiesResponseHeadersData['headers'][$key][] = $values;
            }
        }

        return $this;
    }

    public function withHeaders(array $headers): self
    {
        $this->modifiesResponseHeadersData['headers'] = [];

        foreach ($headers as $key => $values) {
            $this->withHeader($key, $values);
        }

        return $this;
    }

    public function withStatusCode(int $status): self
    {
        if ($status < 100 || $status >= 600) {
            throw new InvalidArgumentException('@todo');
        }

        $this->modifiesResponseHeadersData['status'] = $status;

        return $this;
    }

    protected function initializeModifiesResponseHeaders(): void
    {
        $this->addAction('send_headers', function () {
            if (headers_sent()) {
                return;
            }

            foreach ($this->modifiesResponseHeadersData['headers'] as $key => $values) {
                foreach ($values as $value) {
                    // @todo Should we check $key and force replace param to true for certain headers?
                    // For example, Symfony prevents multiple content type headers.
                    // Should we also set status code as third param when we get around to implementing?
                    header("{$key}: {$value}", false);
                }
            }
        });

        $this->addAction('template_redirect', function () {
            if (! is_int($this->modifiesResponseHeadersData['status'])) {
                return;
            }

            if (headers_sent()) {
                return;
            }

            $status = $this->modifiesResponseHeadersData['status'];
            $description = get_status_header_desc($status);

            if (empty($description)) {
                // @todo Throw?
                return;
            }

            $protocol = wp_get_server_protocol();

            header("{$protocol} {$status} {$description}", true, $status);
        });
    }
}
