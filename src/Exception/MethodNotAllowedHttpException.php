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
        // Our template already has the viewport meta tag - let's prevent duplicates in FSE themes.
        remove_action('wp_head', '_block_template_viewport_meta_tag', 0);

        $errorTemplate = get_query_template('405');

        /**
         * @psalm-suppress DocblockTypeContradiction
         */
        if (! \is_string($errorTemplate) || '' === $errorTemplate) {
            $errorTemplate = realpath(__DIR__ . '/../../templates/405.php');
        }

        return $errorTemplate;
    }

    public function onWpRobots($robots)
    {
        $robots['noindex'] = true;
        $robots['nofollow'] = true;
        $robots['noarchive'] = true;

        return $robots;
    }

    protected function doPrepareResponse(HttpExceptionResponder $responder): void
    {
        $responder->headers()->includeNocacheHeaders();

        $responder->theme()
            ->addBodyClass('error405')
            ->setTitle('Method not allowed');

        $responder->wpQuery()->resetFlags();

        add_filter('template_include', [$this, 'onTemplateInclude']);
        add_filter('wp_robots', [$this, 'onWpRobots']);
    }
}
