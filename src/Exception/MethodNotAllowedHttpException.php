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

    /**
     * @param mixed $wpQuery
     */
    public function onParseQuery($wpQuery): void
    {
        if ($wpQuery instanceof WP_Query) {
            // Is this necessary or would it be sufficient just to set $wpQuery->is_home = false?
            // Or would it be better to use the 'parse_request' filter and unset all query variables
            // before they ever get to $wpQuery?
            $wpQuery->init_query_flags();
        }
    }

    public function onSendHeaders(): void
    {
        nocache_headers();
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
            ->withBodyClass('error405')
            ->withTitle('Method not allowed');

        // @todo Can existing responder traits be adapted to handle these?
        $responder
            ->withAction('parse_query', [$this, 'onParseQuery'])
            ->withAction('send_headers', [$this, 'onSendHeaders'])
            ->withFilter('template_include', [$this, 'onTemplateInclude']);
    }
}
