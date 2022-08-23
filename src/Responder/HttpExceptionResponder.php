<?php

declare(strict_types=1);

namespace ToyWpRouting\Responder;

use ToyWpRouting\Exception\HttpExceptionInterface;
use ToyWpRouting\Responder\Concerns\ModifiesResponseHeaders;
use ToyWpRouting\Responder\Concerns\ModifiesResponseHtml;
use ToyWpRouting\Responder\Concerns\ModifiesWpParameters;
use ToyWpRouting\Responder\Concerns\ModifiesWpQueryParameters;
use ToyWpRouting\Responder\Concerns\SendsJsonResponses;
use ToyWpRouting\Responder\Concerns\SendsRedirectResponses;

class HttpExceptionResponder extends HookDrivenResponder
{
    use ModifiesResponseHeaders;
    use ModifiesResponseHtml;
    use ModifiesWpParameters;
    use ModifiesWpQueryParameters;
    use SendsJsonResponses;
    use SendsRedirectResponses;

    public function __construct(HttpExceptionInterface $exception)
    {
        $exception->prepareResponse($this);
    }
}
