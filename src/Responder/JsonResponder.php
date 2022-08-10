<?php

declare(strict_types=1);

namespace ToyWpRouting\Responder;

use ToyWpRouting\Responder\Concerns\ModifiesResponseHeaders;
use ToyWpRouting\Responder\Concerns\SendsJsonResponses;

class JsonResponder extends HookDrivenResponder
{
    use ModifiesResponseHeaders;
    use SendsJsonResponses;

    public function __construct(array $data, int $statusCode = 200, int $options = 0)
    {
        $this->withJsonData($data)
            ->withJsonStatusCode($statusCode)
            ->withJsonOptions($options);
    }
}
