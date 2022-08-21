<?php

declare(strict_types=1);

namespace ToyWpRouting\Exception;

use Throwable;
use ToyWpRouting\Responder\HttpExceptionResponder;
use WP_Query;

class MethodNotAllowedHttpException extends HttpException
{
    public function __construct(
        array $allowedMethods,
        string $message = '',
        ?Throwable $previous = null,
        int $code = 0,
        array $headers = []
    ) {
        $headers['Allow'] = strtoupper(implode(', ', $allowedMethods));

        parent::__construct(405, $message, $previous, $headers, $code);
    }

    public function onTemplateInclude(): string
    {
        // Alternatively we can allow wordpress to handle status header for us by setting the
        // 'error' query variable on the global wp instance to '405' within the 'parse_request'
        // action. However, the status header will generally be set back to 200 in $wp->handle_404()
        // so we would also need to filter 'pre_handle_404' to prevent this.
        $errorTemplate = get_query_template('405');

        // Alternatively we might want to just fall back to the theme index template...
        if (! \is_string($errorTemplate) || '' === $errorTemplate) {
            $errorTemplate = realpath(__DIR__ . '/../../templates/405.php');
        }

        return $errorTemplate;
    }

    protected function doPrepareResponse(HttpExceptionResponder $responder): void
    {
        $responder
            ->withAllQueryFlagsReset()
            ->withBodyClass('error405')
            ->withNocacheHeaders()
            ->withTitle('Method not allowed');

        $responder->withFilter('template_include', [$this, 'onTemplateInclude']);
    }
}
