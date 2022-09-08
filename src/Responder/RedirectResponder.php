<?php

declare(strict_types=1);

namespace ToyWpRouting\Responder;

use ToyWpRouting\Responder\Partial\HeadersPartial;
use ToyWpRouting\Responder\Partial\RedirectPartial;

class RedirectResponder extends ComposableResponder
{
    public function __construct(
        string $location,
        int $statusCode = 302,
        string $redirectByHeader = 'WordPress',
        bool $safe = true
    ) {
        $this->redirect()
            ->setLocation($location)
            ->setStatusCode($statusCode)
            ->setInitiator($redirectByHeader);

        if (! $safe) {
            $this->redirect()->allowUnsafeRedirects();
        }
    }

    public function headers(): HeadersPartial
    {
        return $this->getPartialSet()->get(HeadersPartial::class);
    }

    public function redirect(): RedirectPartial
    {
        return $this->getPartialSet()->get(RedirectPartial::class);
    }

    protected function createPartials(): array
    {
        return [
            new HeadersPartial(),
            new RedirectPartial(),
        ];
    }
}
