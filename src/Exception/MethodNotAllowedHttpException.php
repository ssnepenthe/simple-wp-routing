<?php

declare(strict_types=1);

namespace ToyWpRouting\Exception;

use Throwable;
use ToyWpRouting\Responder\HttpExceptionResponder;

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
        $errorTemplate = get_query_template('405');

        /**
         * @psalm-suppress DocblockTypeContradiction
         */
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
