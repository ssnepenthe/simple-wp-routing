<?php

declare(strict_types=1);

namespace ToyWpRouting\Responder\Partial;

use InvalidArgumentException;

final class RedirectPartial implements PartialInterface, RegistersConflictsInterface
{
    use PartialTrait;

    private string $initiator = 'WordPress';
    private ?string $location = null;
    /**
     * @var ?callable(string,int,string):bool
     */
    private $responseFunction = null;
    private bool $safe = true;
    private int $statusCode = 302;

    public function allowUnsafeRedirects(): self
    {
        $this->safe = false;

        return $this;
    }

    public function dontAllowUnsafeRedirects(): self
    {
        $this->safe = true;

        return $this;
    }

    public function hasLocation(): bool
    {
        return is_string($this->location);
    }

    /**
     * @internal
     */
    public function onTemplateRedirect(): void
    {
        if (! is_string($this->location)) {
            return;
        }

        if (is_callable($this->responseFunction)) {
            $success = ($this->responseFunction)(
                $this->location,
                $this->statusCode,
                $this->initiator
            );
        } elseif ($this->safe) {
            $success = wp_safe_redirect($this->location, $this->statusCode, $this->initiator);
        } else {
            $success = wp_redirect($this->location, $this->statusCode, $this->initiator);
        }

        if ($success) {
            exit;
        }
    }

    /**
     * @internal
     */
    public function registerConflicts(Conflicts $conflicts): void
    {
        $conflicts
            ->register([static::class, 'hasLocation'], [AssetsPartial::class, 'isModifyingResponse'])
            ->register([static::class, 'hasLocation'], [HeadersPartial::class, 'hasStatusCode'])
            ->register([static::class, 'hasLocation'], [JsonPartial::class, 'hasData'])
            ->register([static::class, 'hasLocation'], [ResponsePartial::class, 'hasBody'])
            ->register([static::class, 'hasLocation'], [TemplatePartial::class, 'hasTemplate'])
            ->register([static::class, 'hasLocation'], [ThemePartial::class, 'isModifyingResponse']);
    }

    /**
     * @internal
     */
    public function respond(): void
    {
        add_action('template_redirect', [$this, 'onTemplateRedirect']);
    }

    public function setInitiator(string $initiator): self
    {
        $this->initiator = $initiator;

        return $this;
    }

    public function setLocation(string $location): self
    {
        $this->location = $location;

        return $this;
    }

    /**
     * @param callable(string,int,string):bool $responseFunction
     */
    public function setResponseFunction(?callable $responseFunction): self
    {
        $this->responseFunction = $responseFunction;

        return $this;
    }

    public function setStatusCode(int $statusCode): self
    {
        if (! ($statusCode >= 300 && $statusCode < 400)) {
            throw new InvalidArgumentException('Redirect status code must be between 300 and 399');
        }

        $this->statusCode = $statusCode;

        return $this;
    }
}
