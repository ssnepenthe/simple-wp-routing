<?php

declare(strict_types=1);

namespace ToyWpRouting\Responder;

use ToyWpRouting\Responder\Partial\HeadersPartial;
use ToyWpRouting\Responder\Partial\JsonPartial;

class JsonResponder extends ComposableResponder
{
    /**
     * @param mixed $data
     */
    public function __construct($data, int $statusCode = 200, int $options = 0)
    {
        $this->json()
            ->setData($data)
            ->setStatusCode($statusCode)
            ->setOptions($options);
    }

    public function headers(): HeadersPartial
    {
        return $this->getPartialSet()->get(HeadersPartial::class);
    }

    public function json(): JsonPartial
    {
        return $this->getPartialSet()->get(JsonPartial::class);
    }

    protected function createPartials(): array
    {
        return [
            new HeadersPartial(),
            new JsonPartial(),
        ];
    }
}
