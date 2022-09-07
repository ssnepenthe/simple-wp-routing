<?php

declare(strict_types=1);

namespace ToyWpRouting\Responder\Partial;

// @todo Hierarchy? query template?
final class TemplatePartial implements PartialInterface, RegistersConflictsInterface
{
    use PartialTrait;

    private string $template = '';

    public function hasTemplate(): bool
    {
        return '' !== $this->template;
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
        if ('' === $this->template || ! is_string($template)) {
            return $template;
        }

        return $this->template;
    }

    /**
     * @internal
     */
    public function registerConflicts(PartialSet $partialSet): void
    {
        $partialSet->addConflict([static::class, 'hasTemplate'], [JsonPartial::class, 'hasData']);

        $partialSet->addConflict(
            [static::class, 'hasTemplate'],
            [RedirectPartial::class, 'hasLocation']
        );

        $partialSet->addConflict(
            [static::class, 'hasTemplate'],
            [ResponsePartial::class, 'hasBody']
        );
    }

    /**
     * @internal
     */
    public function respond(): void
    {
        add_filter('template_include', [$this, 'onTemplateInclude']);
    }

    public function setTemplate(string $templatePath): self
    {
        // @todo verify file exists/is readable?
        $this->template = $templatePath;

        return $this;
    }
}
