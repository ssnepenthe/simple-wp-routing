<?php

namespace ToyWpRouting\Exception;

use RuntimeException;

class RewriteDisabledException extends RuntimeException
{
    public function toHttpException(): NotFoundHttpException
    {
        return new NotFoundHttpException('', $this);
    }
}
