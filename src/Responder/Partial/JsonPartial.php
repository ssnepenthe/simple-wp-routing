<?php

declare(strict_types=1);

namespace ToyWpRouting\Responder\Partial;

use InvalidArgumentException;

final class JsonPartial implements PartialInterface, RegistersConflictsInterface
{
    use PartialTrait;

    /**
     * @var mixed
     */
    private $data;
    private bool $envelopeResponse = true;
    private bool $hasData = false;
    private int $options = 0;
    /**
     * @var ?callable(mixed,int,int):never
     */
    private $responseFunction = null;
    private int $statusCode = 200;

    public function dontEnvelopeResponse(): self
    {
        $this->envelopeResponse = false;

        return $this;
    }

    public function envelopeResponse(): self
    {
        $this->envelopeResponse = true;

        return $this;
    }

    public function hasData(): bool
    {
        return $this->hasData;
    }

    /**
     * @internal
     */
    public function onTemplateRedirect(): void
    {
        if (! $this->hasData) {
            return;
        }

        if (is_callable($this->responseFunction)) {
            ($this->responseFunction)($this->data, $this->statusCode, $this->options);
        } elseif (! $this->envelopeResponse) {
            wp_send_json($this->data, $this->statusCode, $this->options);
        } elseif ($this->statusCode >= 200 && $this->statusCode < 300) {
            wp_send_json_success($this->data, $this->statusCode, $this->options);
        } else {
            wp_send_json_error($this->data, $this->statusCode, $this->options);
        }
    }

    /**
     * @internal
     */
    public function registerConflicts(Conflicts $conflicts): void
    {
        $conflicts
            ->register([static::class, 'hasData'], [AssetsPartial::class, 'isModifyingResponse'])
            ->register([static::class, 'hasData'], [HeadersPartial::class, 'hasStatusCode'])
            ->register([static::class, 'hasData'], [RedirectPartial::class, 'hasLocation'])
            ->register([static::class, 'hasData'], [ResponsePartial::class, 'hasBody'])
            ->register([static::class, 'hasData'], [TemplatePartial::class, 'hasTemplate'])
            ->register([static::class, 'hasData'], [ThemePartial::class, 'isModifyingResponse']);
    }

    /**
     * @internal
     */
    public function respond(): void
    {
        add_action('template_redirect', [$this, 'onTemplateRedirect']);
    }

    /**
     * @param mixed $data
     */
    public function setData($data): self
    {
        $this->data = $data;
        $this->hasData = true;

        return $this;
    }

    public function setOptions(int $options): self
    {
        $this->options = $options;

        return $this;
    }

    /**
     * @psalm-param callable(mixed,int,int):never $responseFunction
     */
    public function setResponseFunction(?callable $responseFunction): self
    {
        $this->responseFunction = $responseFunction;

        return $this;
    }

    public function setStatusCode(int $statusCode): self
    {
        // 1xx responses shouldn't have a body - should we allow them here anyway?
        if ($statusCode < 200 || ($statusCode >= 300 && $statusCode < 400) || $statusCode >= 600) {
            throw new InvalidArgumentException(
                'JSON response code must be between 200 and 299 or between 400 and 599'
            );
        }

        $this->statusCode = $statusCode;

        return $this;
    }
}
