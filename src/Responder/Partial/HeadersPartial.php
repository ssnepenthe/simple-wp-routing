<?php

declare(strict_types=1);

namespace SimpleWpRouting\Responder\Partial;

use InvalidArgumentException;
use RuntimeException;

final class HeadersPartial implements PartialInterface, RegistersConflictsInterface
{
    use PartialTrait;

    private array $headers = [];

    private bool $includeNocacheHeaders = false;

    private ?int $statusCode = null;

    private ?string $statusDescription = null;

    /**
     * @param string|string[] $values
     */
    public function addHeader(string $key, $values, bool $replace = true): self
    {
        if (is_array($values)) {
            $values = array_values($values);

            if (true === $replace || ! isset($this->headers[$key])) {
                $this->headers[$key] = $values;
            } else {
                $this->headers[$key] = array_merge($this->headers[$key], $values);
            }
        } else {
            if (true === $replace || ! isset($this->headers[$key])) {
                $this->headers[$key] = [$values];
            } else {
                $this->headers[$key][] = $values;
            }
        }

        return $this;
    }

    public function addHeaders(array $headers, bool $replace = true): self
    {
        foreach ($headers as $key => $values) {
            $this->addHeader($key, $values, $replace);
        }

        return $this;
    }

    public function dontIncludeNocacheHeaders(): self
    {
        $this->includeNocacheHeaders = false;

        return $this;
    }

    public function hasHeader(string $key): bool
    {
        return array_key_exists($key, $this->headers) && [] !== $this->headers[$key];
    }

    public function hasHeaders(): bool
    {
        return [] !== $this->headers;
    }

    public function hasStatusCode(): bool
    {
        return is_int($this->statusCode);
    }

    public function includeNocacheHeaders(): self
    {
        $this->includeNocacheHeaders = true;

        return $this;
    }

    public function isIncludingNocacheHeaders(): bool
    {
        return $this->includeNocacheHeaders;
    }

    public function isModifyingResponse(): bool
    {
        return $this->hasHeaders() || $this->hasStatusCode() || $this->isIncludingNocacheHeaders();
    }

    /**
     * @internal
     */
    public function onSendHeaders(): void
    {
        if (! $this->isModifyingResponse() || headers_sent()) {
            return;
        }

        foreach ($this->headers as $key => $values) {
            foreach ($values as $value) {
                header("{$key}: {$value}", false);
            }
        }

        if ($this->includeNocacheHeaders) {
            nocache_headers();
        }
    }

    /**
     * @internal
     */
    public function onTemplateRedirect(): void
    {
        if (headers_sent()) {
            return;
        }

        if (is_int($this->statusCode)) {
            if (is_string($this->statusDescription)) {
                $description = $this->statusDescription;
            } else {
                $description = get_status_header_desc($this->statusCode);
            }

            if ('' === $description) {
                throw new RuntimeException(
                    "Unable to get description for status code \"{$this->statusCode}\""
                    . ' - set manually using HeadersPartial::setStatusDescription()'
                );
            }

            $protocol = wp_get_server_protocol();

            header("{$protocol} {$this->statusCode} {$description}", true, $this->statusCode);
        }
    }

    /**
     * @internal
     */
    public function registerConflicts(Conflicts $conflicts): void
    {
        $conflicts
            ->register([static::class, 'hasStatusCode'], [JsonPartial::class, 'hasData'])
            ->register([static::class, 'hasStatusCode'], [RedirectPartial::class, 'hasLocation']);
    }

    public function removeHeader(string $key): self
    {
        unset($this->headers[$key]);

        return $this;
    }

    public function removeHeaders(array $keys): self
    {
        foreach ($keys as $key) {
            unset($this->headers[$key]);
        }

        return $this;
    }

    /**
     * @internal
     */
    public function respond(): void
    {
        add_action('send_headers', [$this, 'onSendHeaders']);
        add_action('template_redirect', [$this, 'onTemplateRedirect']);
    }

    public function setHeaders(array $headers): self
    {
        $this->headers = [];

        foreach ($headers as $key => $values) {
            $this->addHeader($key, $values);
        }

        return $this;
    }

    public function setStatusCode(?int $statusCode): self
    {
        if (is_int($statusCode) && ($statusCode < 100 || $statusCode >= 600)) {
            throw new InvalidArgumentException('Invalid status code - must be between 100 and 599');
        }

        $this->statusCode = $statusCode;

        return $this;
    }

    public function setStatusDescription(?string $statusDescription): self
    {
        $this->statusDescription = $statusDescription;

        return $this;
    }
}
