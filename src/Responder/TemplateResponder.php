<?php

declare(strict_types=1);

namespace ToyWpRouting\Responder;

use ToyWpRouting\Responder\Partial\TemplatePartial;

class TemplateResponder extends Responder
{
    public function __construct(string $templatePath)
    {
        $this->getPartialSet()->get(TemplatePartial::class)->setTemplate($templatePath);
    }
}
