<?php

declare(strict_types=1);

namespace ToyWpRouting\Responder;

class RedirectResponder extends HookDrivenResponder
{
    use ModifiesResponseHeaders;
    use SendsRedirectResponses;

    public function __construct(
        string $location,
        int $statusCode = 302,
        string $redirectByHeader = 'WordPress',
        bool $safe = true
    ) {
        $this->withRedirectLocation($location)
            ->withRedirectStatusCode($statusCode)
            ->withRedirectByHeader($redirectByHeader);

        if (! $safe) {
            $this->withUnsafeRedirectsAllowed();
        }
    }
}
