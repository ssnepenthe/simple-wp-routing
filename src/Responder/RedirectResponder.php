<?php

declare(strict_types=1);

namespace SimpleWpRouting\Responder;

use SimpleWpRouting\Responder\Partial\RedirectPartial;

final class RedirectResponder extends Responder
{
    /**
     * @param int<300, 399> $statusCode
     */
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
