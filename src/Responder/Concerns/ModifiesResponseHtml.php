<?php

declare(strict_types=1);

namespace ToyWpRouting\Responder;

// @todo ModifiesResponseTemplateHtml? enqueue scripts and style? 404 preempt?
trait ModifiesResponseHtml
{
    protected array $modifiesResponseHtmlData = [
        'bodyClasses' => [],
        'title' => null,
        'template' => null,
    ];

    public function withBodyClass(string $bodyClass): self
    {
        $this->modifiesResponseHtmlData['bodyClasses'][] = $bodyClass;

        return $this;
    }

    public function withBodyClasses(array $bodyClasses): self
    {
        $this->modifiesResponseHtmlData['bodyClasses'] = array_values(
            (fn (string ...$bodyClasses) => $bodyClasses)(...$bodyClasses)
        );

        return $this;
    }

    public function withTemplate(string $templatePath): self
    {
        $this->modifiesResponseHtmlData['template'] = $templatePath;

        return $this;
    }

    public function withTitle(string $title): self
    {
        $this->modifiesResponseHtmlData['title'] = $title;

        return $this;
    }

    protected function initializeModifiesResponseHtml(): void
    {
        $this->addFilter('body_class', function ($classes) {
            if (is_array($classes) && ! empty($this->modifiesResponseHtmlData['bodyClasses'])) {
                $classes = array_merge($classes, $this->modifiesResponseHtmlData['bodyClasses']);
            }

            return $classes;
        });

        $this->addFilter('document_title_parts', function ($parts) {
            if (is_array($parts) && is_string($this->modifiesResponseHtmlData['title'])) {
                $parts['title'] = $this->modifiesResponseHtmlData['title'];
            }

            return $parts;
        });

        $this->addFilter('template_include', function ($template) {
            if (is_string($this->modifiesResponseHtmlData['template'])) {
                return $this->modifiesResponseHtmlData['template'];
            }

            return $template;
        });
    }

    protected function isModifyingResponseHtmlTemplate(): bool
    {
        return is_string($this->modifiesResponseHtmlData['template']);
    }
}
