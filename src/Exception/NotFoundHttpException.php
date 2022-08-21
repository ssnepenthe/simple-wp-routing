<?php

declare(strict_types=1);

namespace ToyWpRouting\Exception;

use Throwable;
use ToyWpRouting\Responder\HttpExceptionResponder;
use WP_Query;

class NotFoundHttpException extends HttpException
{
    public function __construct(
        string $message = '',
        ?Throwable $previous = null,
        int $code = 0,
        array $headers = []
    ) {
        parent::__construct(404, $message, $previous, $headers, $code);
    }

    public function onParseQuery(WP_Query $wpQuery): void
    {
        $wpQuery->set_404();
    }

    public function onSendHeaders(): void
    {
        nocache_headers();
    }

    protected function doPrepareResponse(HttpExceptionResponder $responder): void
    {
        $responder
            ->withAction('parse_query', [$this, 'onParseQuery'])
            ->withAction('send_headers', [$this, 'onSendHeaders']);
    }
}