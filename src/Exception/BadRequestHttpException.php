<?php

namespace ToyWpRouting\Exception;

use Throwable;
use ToyWpRouting\Responder\HttpExceptionResponder;
use ToyWpRouting\Responder\Partial\HeadersPartial;
use ToyWpRouting\Responder\Partial\ThemePartial;
use ToyWpRouting\Responder\Partial\WpQueryPartial;

class BadRequestHttpException extends HttpException
{
    public function __construct(
        string $message = '',
        ?Throwable $previous = null,
        int $code = 0,
        array $headers = []
    ) {
        parent::__construct(400, $message, $previous, $headers, $code);
    }

    public function onTemplateInclude(): string
    {
        // Our template already has the viewport meta tag - let's prevent duplicates in FSE themes.
        remove_action('wp_head', '_block_template_viewport_meta_tag', 0);

        $errorTemplate = get_query_template('400');

        /**
         * @psalm-suppress DocblockTypeContradiction
         */
        if (! \is_string($errorTemplate) || '' === $errorTemplate) {
            $errorTemplate = realpath(__DIR__ . '/../../templates/400.php');
        }

        return $errorTemplate;
    }

    /**
     * @param array $robots
     * @return array
     */
    public function onWpRobots($robots)
    {
        $robots['noindex'] = true;
        $robots['nofollow'] = true;
        $robots['noarchive'] = true;

        return $robots;
    }

    protected function doPrepareResponse(HttpExceptionResponder $responder): void
    {
        $responder->getPartialSet()->get(HeadersPartial::class)->includeNocacheHeaders();

        $responder->getPartialSet()
            ->get(ThemePartial::class)
            ->addBodyClass('error404')
            ->setTitle('Bad request');

        $responder->getPartialSet()->get(WpQueryPartial::class)->resetFlags();

        add_filter('template_include', [$this, 'onTemplateInclude']);
        add_filter('wp_robots', [$this, 'onWpRobots']);
    }
}
