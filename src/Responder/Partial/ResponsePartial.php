<?php

declare(strict_types=1);

namespace ToyWpRouting\Responder\Partial;

final class ResponsePartial implements PartialInterface, RegistersConflictsInterface
{
    use PartialTrait;

    private ?string $body = null;

    public function hasBody(): bool
    {
        return is_string($this->body);
    }

    /**
     * @param string $template
     *
     * @return string
     *
     * @internal
     */
    public function onTemplateInclude($template)
    {
        /**
         * @psalm-suppress DocblockTypeContradiction
         */
        if (! is_string($this->body) || ! is_string($template)) {
            return $template;
        }

        echo $this->body;

        return dirname(__DIR__, 3) . '/templates/blank.php';
    }

    /**
     * @internal
     */
    public function registerConflicts(PartialSet $partialSet): void
    {
        $partialSet->addConflict([static::class, 'hasBody'], [JsonPartial::class, 'hasData']);

        $partialSet->addConflict(
            [static::class, 'hasBody'],
            [RedirectPartial::class, 'hasLocation']
        );

        $partialSet->addConflict(
            [static::class, 'hasBody'],
            [TemplatePartial::class, 'hasTemplate']
        );
    }

    /**
     * @internal
     */
    public function respond(): void
    {
        add_filter('template_include', [$this, 'onTemplateInclude']);
    }

    public function setBody(?string $body): self
    {
        $this->body = $body;

        return $this;
    }
}
