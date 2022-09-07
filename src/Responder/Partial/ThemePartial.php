<?php

declare(strict_types=1);

namespace ToyWpRouting\Responder\Partial;

final class ThemePartial implements PartialInterface, RegistersConflictsInterface
{
    use PartialTrait;

    private array $bodyClasses = [];
    private string $title = '';

    public function addBodyClass(string $class): self
    {
        $this->bodyClasses[$class] = null;

        return $this;
    }

    public function addBodyClasses(array $classes): self
    {
        foreach ($classes as $class) {
            $this->addBodyClass($class);
        }

        return $this;
    }

    public function isModifyingResponse(): bool
    {
        return [] !== $this->bodyClasses || '' !== $this->title;
    }

    /**
     * @param array $classes
     *
     * @return array
     *
     * @internal
     */
    public function onBodyClass($classes)
    {
        /**
         * @psalm-suppress DocblockTypeContradiction
         */
        if (empty($this->bodyClasses) || ! is_array($classes)) {
            return $classes;
        }

        return array_merge($classes, array_keys($this->bodyClasses));
    }

    /**
     * @param array $parts
     *
     * @return array
     *
     * @internal
     */
    public function onDocumentTitleParts($parts)
    {
        /**
         * @psalm-suppress DocblockTypeContradiction
         */
        if ('' === $this->title || ! is_array($parts)) {
            return $parts;
        }

        $parts['title'] = $this->title;

        return $parts;
    }

    /**
     * @internal
     */
    public function registerConflicts(PartialSet $partialSet): void
    {
        $partialSet->addConflict(
            [static::class, 'isModifyingResponse'],
            [JsonPartial::class, 'hasData']
        );

        $partialSet->addConflict(
            [static::class, 'isModifyingResponse'],
            [RedirectPartial::class, 'hasLocation']
        );
    }

    /**
     * @internal
     */
    public function respond(): void
    {
        add_filter('body_class', [$this, 'onBodyClass']);
        add_filter('document_title_parts', [$this, 'onDocumentTitleParts']);
    }

    public function setBodyClasses(array $classes): self
    {
        $this->bodyClasses = array_combine(
            (fn (string ...$classes) => $classes)(...$classes),
            array_fill(0, count($classes), null)
        );

        return $this;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }
}
