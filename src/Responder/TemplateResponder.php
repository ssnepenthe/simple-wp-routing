<?php

declare(strict_types=1);

namespace ToyWpRouting\Responder;

// @todo modifies wp? modifies wp query?
class TemplateResponder extends HookDrivenResponder
{
    use ModifiesResponseHtml;

    public function __construct(string $templatePath)
    {
        $this->withTemplate($templatePath);
    }
}
