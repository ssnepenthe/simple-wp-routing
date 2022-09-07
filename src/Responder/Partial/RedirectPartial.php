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
            // @todo Should this also receive value of $this->safe?
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
    public function registerConflicts(PartialSet $partialSet): void
    {
        // @todo headers containing x-redirect-by?
        $partialSet->addConflict(
            [static::class, 'hasLocation'],
            [AssetsPartial::class, 'isModifyingResponse']
        );

        $partialSet->addConflict(
            [static::class, 'hasLocation'],
            [HeadersPartial::class, 'hasStatusCode']
        );

        $partialSet->addConflict([static::class, 'hasLocation'], [JsonPartial::class, 'hasData']);

        $partialSet->addConflict(
            [static::class, 'hasLocation'],
            [ResponsePartial::class, 'hasBody']
        );

        $partialSet->addConflict(
            [static::class, 'hasLocation'],
            [TemplatePartial::class, 'hasTemplate']
        );

        $partialSet->addConflict(
            [static::class, 'hasLocation'],
            [ThemePartial::class, 'isModifyingResponse']
        );
    }

    /**
     * @internal
     */
    public function respond(): void
    {
        add_action('template_redirect', [$this, 'onTemplateRedirect']);
    }

    // @todo name?
    public function setInitiator(string $initiator): self
    {
        $this->initiator = $initiator;

        return $this;
    }

    // @todo to() method as an alias?
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
