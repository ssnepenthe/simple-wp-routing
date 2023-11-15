<?php

declare(strict_types=1);

namespace SimpleWpRouting\Exception;

use Throwable;
use SimpleWpRouting\Responder\HttpExceptionResponder;
use SimpleWpRouting\Responder\Partial\HeadersPartial;
use WP_Query;

final class NotFoundHttpException extends HttpException
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

    protected function doPrepareResponse(HttpExceptionResponder $responder): void
    {
        $responder->getPartialSet()->get(HeadersPartial::class)->includeNocacheHeaders();

        add_action('parse_query', [$this, 'onParseQuery']);
    }
}
