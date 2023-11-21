<?php

declare(strict_types=1);

namespace SimpleWpRouting\Responder;

use SimpleWpRouting\Responder\Partial\TemplatePartial;

final class TemplateResponder extends Responder
{
    public function __construct(string $templatePath)
    {
        $this->getPartialSet()->get(TemplatePartial::class)->setTemplate($templatePath);
    }
}
