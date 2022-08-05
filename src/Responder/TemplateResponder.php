<?php

declare(strict_types=1);

namespace ToyWpRouting\Responder;

use ToyWpRouting\Responder\Concerns\ModifiesResponseHeaders;
use ToyWpRouting\Responder\Concerns\ModifiesResponseHtml;
use ToyWpRouting\Responder\Concerns\ModifiesWpParameters;
use ToyWpRouting\Responder\Concerns\ModifiesWpQueryParameters;

class TemplateResponder extends HookDrivenResponder
{
    use ModifiesResponseHeaders;
    use ModifiesResponseHtml;
    use ModifiesWpParameters;
    use ModifiesWpQueryParameters;

    public function __construct(string $templatePath)
    {
        $this->withTemplate($templatePath);
    }
}
