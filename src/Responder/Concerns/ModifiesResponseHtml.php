<?php

declare(strict_types=1);

namespace ToyWpRouting\Responder\Concerns;

// @todo 404 preempt?
trait ModifiesResponseHtml
{
    protected array $modifiesResponseHtmlData = [
        'bodyClasses' => [],
        'enqueuedScripts' => [],
        'enqueuedStyles' => [],
        'title' => null,
        'template' => null,
    ];

    public function withAdditionalEnqueuedScripts(array $handles): self
    {
        foreach ($handles as $handle) {
            $this->withEnqueuedScript($handle);
        }

        return $this;
    }

    public function withAdditionalEnqueuedStyles(array $handles): self
    {
        foreach ($handles as $handle) {
            $this->withEnqueuedStyle($handle);
        }

        return $this;
    }

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

    public function withEnqueuedScript(string $handle): self
    {
        $this->modifiesResponseHtmlData['enqueuedScripts'][] = $handle;

        return $this;
    }

    public function withEnqueuedScripts(array $handles): self
    {
        $this->modifiesResponseHtmlData['enqueuedScripts'] = array_values(
            (fn (string ...$handles) => $handles)(...$handles)
        );

        return $this;
    }

    public function withEnqueuedStyle(string $handle): self
    {
        $this->modifiesResponseHtmlData['enqueuedStyles'][] = $handle;

        return $this;
    }

    public function withEnqueuedStyles(array $handles): self
    {
        $this->modifiesResponseHtmlData['enqueuedStyles'] = array_values(
            (fn (string ...$handles) => $handles)(...$handles)
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
        $this->addAction('wp_enqueue_scripts', function () {
            foreach ($this->modifiesResponseHtmlData['enqueuedScripts'] as $handle) {
                wp_enqueue_script($handle);
            }

            foreach ($this->modifiesResponseHtmlData['enqueuedStyles'] as $handle) {
                wp_enqueue_style($handle);
            }
        });

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
            if (! is_string($this->modifiesResponseHtmlData['template'])) {
                return $template;
            }

            return $this->modifiesResponseHtmlData['template'];
        });

        $this->addConflictCheck(function () {
            if (! $this->isModifyingResponseHtml()) {
                return;
            }

            if (method_exists($this, 'isSendingJsonResponse') && $this->isSendingJsonResponse()) {
                return 'Cannot modify response HTML and send JSON response at the same time';
            }

            if (
                method_exists($this, 'isSendingDirectResponse')
                && $this->isSendingDirectResponse()
            ) {
                return 'Cannot modify response HTML and send direct response at the same time';
            }

            if (
                method_exists($this, 'isSendingRedirectResponse')
                && $this->isSendingRedirectResponse()
            ) {
                return 'Cannot modify response HTML and send redirect response at the same time';
            }
        });
    }

    protected function isModifyingResponseHtml(): bool
    {
        return ! empty($this->modifiesResponseHtmlData['bodyClasses'])
            || ! empty($this->modifiesResponseHtmlData['enqueuedScripts'])
            || ! empty($this->modifiesResponseHtmlData['enqueuedStyles'])
            || is_string($this->modifiesResponseHtmlData['title'])
            || is_string($this->modifiesResponseHtmlData['template']);
    }
}
