<?php

namespace ToyWpRouting\Responder;

use ToyWpRouting\Responder\Partial;

class Responder extends ComposableResponder
{
    protected function createPartials(): array
    {
        return [
            new Partial\AssetsPartial(),
            new Partial\HeadersPartial(),
            new Partial\JsonPartial(),
            new Partial\RedirectPartial(),
            new Partial\ResponsePartial(),
            new Partial\TemplatePartial(),
            new Partial\ThemePartial(),
            new Partial\WpPartial(),
            new Partial\WpQueryPartial(),
        ];
    }
}
