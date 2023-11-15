<?php

declare(strict_types=1);

namespace SimpleWpRouting\Responder\Partial;

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
    public function registerConflicts(Conflicts $conflicts): void
    {
        $conflicts
            ->register([static::class, 'hasTemplate'], [JsonPartial::class, 'hasData'])
            ->register([static::class, 'hasTemplate'], [RedirectPartial::class, 'hasLocation'])
            ->register([static::class, 'hasTemplate'], [ResponsePartial::class, 'hasBody']);
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
        $this->template = $templatePath;

        return $this;
    }
}
