<?php

declare(strict_types=1);

namespace ToyWpRouting\Responder;

use ToyWpRouting\Responder\Partial\JsonPartial;

final class JsonResponder extends Responder
{
    /**
     * @param mixed $data
     * @param int<200, 299>|int<400, 599> $statusCode
     */
    public function __construct($data, int $statusCode = 200, int $options = 0)
    {
        $this->getPartialSet()
            ->get(JsonPartial::class)
            ->setData($data)
            ->setStatusCode($statusCode)
            ->setOptions($options);
    }
}
