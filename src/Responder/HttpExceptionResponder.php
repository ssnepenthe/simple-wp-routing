<?php

declare(strict_types=1);

namespace ToyWpRouting\Responder;

use ToyWpRouting\Exception\HttpExceptionInterface;
use ToyWpRouting\Responder\Partial\AssetsPartial;
use ToyWpRouting\Responder\Partial\HeadersPartial;
use ToyWpRouting\Responder\Partial\JsonPartial;
use ToyWpRouting\Responder\Partial\RedirectPartial;
use ToyWpRouting\Responder\Partial\TemplatePartial;
use ToyWpRouting\Responder\Partial\ThemePartial;
use ToyWpRouting\Responder\Partial\WpPartial;
use ToyWpRouting\Responder\Partial\WpQueryPartial;

class HttpExceptionResponder extends ComposableResponder
{
    public function __construct(HttpExceptionInterface $exception)
    {
        $exception->prepareResponse($this);
    }

    public function assets(): AssetsPartial
    {
        return $this->getPartialSet()->get(AssetsPartial::class);
    }

    public function headers(): HeadersPartial
    {
        return $this->getPartialSet()->get(HeadersPartial::class);
    }

    public function json(): JsonPartial
    {
        return $this->getPartialSet()->get(JsonPartial::class);
    }

    public function redirect(): RedirectPartial
    {
        return $this->getPartialSet()->get(RedirectPartial::class);
    }

    public function template(): TemplatePartial
    {
        return $this->getPartialSet()->get(TemplatePartial::class);
    }

    public function theme(): ThemePartial
    {
        return $this->getPartialSet()->get(ThemePartial::class);
    }

    public function wp(): WpPartial
    {
        return $this->getPartialSet()->get(WpPartial::class);
    }

    public function wpQuery(): WpQueryPartial
    {
        return $this->getPartialSet()->get(WpQueryPartial::class);
    }

    protected function createPartials(): array
    {
        return [
            new AssetsPartial(),
            new HeadersPartial(),
            new JsonPartial(),
            new RedirectPartial(),
            new TemplatePartial(),
            new ThemePartial(),
            new WpPartial(),
            new WpQueryPartial(),
        ];
    }
}
