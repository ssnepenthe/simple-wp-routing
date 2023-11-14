<?php

declare(strict_types=1);

namespace ToyWpRouting\Responder;

use ToyWpRouting\Responder\Partial\RedirectPartial;

final class RedirectResponder extends Responder
{
    public function __construct(
        string $location,
        int $statusCode = 302,
        string $redirectByHeader = 'WordPress',
        bool $safe = true
    ) {
        $redirect = $this->getPartialSet()->get(RedirectPartial::class);

        $redirect->setLocation($location)
            ->setStatusCode($statusCode)
            ->setInitiator($redirectByHeader);

        if (! $safe) {
            $redirect->allowUnsafeRedirects();
        }
    }
}
