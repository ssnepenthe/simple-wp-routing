<?php

declare(strict_types=1);

namespace ToyWpRouting\Responder\Partial;

final class AssetsPartial implements PartialInterface, RegistersConflictsInterface
{
    use PartialTrait;

    private array $dequeuedScripts = [];
    private array $dequeuedStyles = [];
    private array $enqueuedScripts = [];
    private array $enqueuedStyles = [];

    public function dequeueScripts(string ...$handles): self
    {
        foreach ($handles as $handle) {
            $this->dequeuedScripts[$handle] = null;
        }

        return $this;
    }

    public function dequeueStyles(string ...$handles): self
    {
        foreach ($handles as $handle) {
            $this->dequeuedStyles[$handle] = null;
        }

        return $this;
    }

    public function enqueueScripts(string ...$handles): self
    {
        foreach ($handles as $handle) {
            $this->enqueuedScripts[$handle] = null;
        }

        return $this;
    }

    public function enqueueStyles(string ...$handles): self
    {
        foreach ($handles as $handle) {
            $this->enqueuedStyles[$handle] = null;
        }

        return $this;
    }

    public function isModifyingResponse(): bool
    {
        return ! (
            [] === $this->dequeuedScripts
            && [] === $this->dequeuedStyles
            && [] === $this->enqueuedScripts
            && [] === $this->enqueuedStyles
        );
    }

    /**
     * @internal
     */
    public function onEnqueueScripts(): void
    {
        foreach ($this->enqueuedScripts as $handle => $_) {
            wp_enqueue_script($handle);
        }

        foreach ($this->dequeuedScripts as $handle => $_) {
            wp_dequeue_script($handle);
        }

        foreach ($this->enqueuedStyles as $handle => $_) {
            wp_enqueue_style($handle);
        }

        foreach ($this->dequeuedStyles as $handle => $_) {
            wp_dequeue_style($handle);
        }
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
        add_action('wp_enqueue_scripts', [$this, 'onEnqueueScripts']);
    }
}
