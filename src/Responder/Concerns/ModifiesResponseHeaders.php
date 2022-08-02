<?php

declare(strict_types=1);

namespace ToyWpRouting\Responder\Concerns;

// @todo status?
trait ModifiesResponseHeaders
{
    protected array $modifiesResponseHeadersData = [
        'headers' => [],
    ];

    public function withHeader(string $key, string $value): self
    {
        $this->modifiesResponseHeadersData['headers'][$key] = $value;

        return $this;
    }

    public function withHeaders(array $headers): self
    {
        $this->modifiesResponseHeadersData['headers'] = [];

        foreach ($headers as $key => $value) {
            $this->withHeader($key, $value);
        }

        return $this;
    }

    protected function initializeModifiesResponseHeaders(): void
    {
        $this->addFilter('wp_headers', function ($headers) {
            // @todo Handle multiple headers with same key?
            // @todo Return our array of headers if ! is_array($headers)?
            if (is_array($headers) && ! empty($this->modifiesResponseHeadersData['headers'])) {
                $headers = array_merge($headers, $this->modifiesResponseHeadersData['headers']);
            }

            return $headers;
        });
    }
}
