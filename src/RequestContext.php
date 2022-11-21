<?php

declare(strict_types=1);

namespace ToyWpRouting;

use ToyWpRouting\Exception\InvalidMethodOverrideException;

class RequestContext
{
    /**
     * @var array<string, string>
     */
    protected array $headers = [];

    protected string $method;

    /**
     * @param array<string, string> $headers
     */
    public function __construct(string $method, array $headers)
    {
        $this->method = strtoupper($method);

        foreach ($headers as $key => $value) {
            $this->setHeader($key, $value);
        }
    }

    public static function extractHeaders(array $server): array
    {
        $headers = [];

        foreach ($server as $key => $value) {
            if (! is_string($key)) {
                continue;
            }

            $upper = strtoupper($key);

            if (0 === strpos($upper, 'HTTP_')) {
                $headers[substr($key, 5)] = $value;
            } elseif (in_array($upper, ['CONTENT_TYPE', 'CONTENT_LENGTH', 'CONTENT_MD5'], true)) {
                $headers[$key] = $value;
            }
        }

        return $headers;
    }

    public static function fromGlobals(): RequestContext
    {
        return new self($_SERVER['REQUEST_METHOD'] ?? 'GET', self::extractHeaders($_SERVER));
    }

    public function getHeader(string $key, ?string $default = null): ?string
    {
        $key = strtolower(str_replace('_', '-', $key));

        return $this->headers[$key] ?? $default;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getIntendedMethod(): string
    {
        $method = $this->getMethod();

        if ('POST' !== $method) {
            return $method;
        }

        $override = $this->getHeader('X-HTTP-METHOD-OVERRIDE');

        if (! is_string($override)) {
            // @todo Support $_POST['_method'] as well?
            return $method;
        }

        $override = strtoupper($override);
        $allowedOverrides = [
            'GET',
            'HEAD',
            'POST',
            'PUT',
            'DELETE',
            'CONNECT',
            'OPTIONS',
            'PATCH',
            'PURGE',
            'TRACE',
        ];

        if (in_array($override, $allowedOverrides, true)) {
            return $override;
        }

        if (1 !== preg_match('/^[A-Z]++$/D', $override)) {
            throw new InvalidMethodOverrideException("Invalid request method override {$override}");
        }

        return $override;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    protected function setHeader(string $key, string $value): void
    {
        $this->headers[strtolower(str_replace('_', '-', $key))] = $value;
    }
}
