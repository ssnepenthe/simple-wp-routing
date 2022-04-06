<?php

declare(strict_types=1);

namespace ToyWpRouting;

class MethodNotAllowedResponder implements ResponderInterface
{
    protected $allowedMethods;

    public function __construct(array $allowedMethods)
    {
        $this->allowedMethods = $allowedMethods;
    }

    public function onBodyClass($classes)
    {
        if (is_array($classes)) {
            $classes[] = 'error405';
        }

        return $classes;
    }

    public function onDocumentTitleParts($parts)
    {
        if (is_array($parts)) {
            $parts['title'] = 'Method not allowed';
        }

        return $parts;
    }

    public function onParseQuery($wp_query)
    {
        // Is this necessary or would it be sufficient just to set $wp_query->is_home = false?
        // Or would it be better to use the 'parse_request' filter and unset all query variables
        // before they ever get to $wp_query?
        $wp_query->init_query_flags();
    }

    public function onTemplateInclude($_)
    {
        // Alternatively we can allow wordpress to handle status header for us by setting the
        // 'error' query variable on the global wp instance to '405' within the 'parse_request'
        // action. However, the status header will generally be set back to 200 in $wp->handle_404()
        // so we would also need to filter 'pre_handle_404' to prevent this.
        status_header(405);
        nocache_headers();

        $errorTemplate = get_query_template('405');

        // Alternatively we might want to just fall back to the theme index template...
        if ('' === $errorTemplate) {
            $errorTemplate = dirname(__DIR__) . '/templates/405.php';
        }

        return $errorTemplate;
    }

    public function onWpHeaders($headers)
    {
        if (is_array($headers)) {
            $headers['Allow'] = strtoupper(implode(', ', $this->allowedMethods));
        }

        return $headers;
    }

    public function respond()
    {
        add_filter('body_class', [$this, 'onBodyClass']);
        add_filter('document_title_parts', [$this, 'onDocumentTitleParts']);
        add_action('parse_query', [$this, 'onParseQuery']);
        add_filter('template_include', [$this, 'onTemplateInclude']);
        add_filter('wp_headers', [$this, 'onWpHeaders']);
    }
}
