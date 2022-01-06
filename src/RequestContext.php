<?php

namespace ToyWpRouting;

use RuntimeException;

class RequestContext
{
    protected $headers;
    protected $method;

    public function __construct(string $method, array $headers)
    {
        $this->method = strtoupper($method);

        foreach ($headers as $key => $value) {
            $this->setHeader($key, $value);
        }
    }

    public function getHeader($key, $default = null)
    {
        $key = strtolower(str_replace('_', '-', $key));

        return $this->headers[$key] ?? $default;
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function getIntendedMethod()
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

        if (! in_array($override, $allowedOverrides, true)) {
            // @todo Looser override validation? See symfony/http-foundation Request->getMethod().
            // @todo maybe shouldn't throw?
            throw new RuntimeException('@todo');
        }

        return $override;
    }

    public function getMethod()
    {
        return $this->method;
    }

    public static function extractHeaders(array $server)
    {
        $headers = [];

        foreach ($server as $key => $value) {
            $upper = strtoupper($key);

            if (0 === strpos($upper, 'HTTP_')) {
                $headers[substr($key, 5)] = $value;
            } elseif (in_array($upper, ['CONTENT_TYPE', 'CONTENT_LENGTH', 'CONTENT_MD5'], true)) {
                $headers[$key] = $value;
            }
        }

        return $headers;
    }

    public static function fromGlobals()
    {
        return new self($_SERVER['REQUEST_METHOD'], self::extractHeaders($_SERVER));
    }

    protected function setHeader(string $key, string $value)
    {
        $this->headers[strtolower(str_replace('_', '-', $key))] = $value;
    }
}
