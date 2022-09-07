<?php

namespace ToyWpRouting\Tests\Browser\Responder;

use ToyWpRouting\Tests\Browser\TestCase;

class TemplateResponderTest extends TestCase
{
    public function testTemplateOnly()
    {
        $browser = $this->getBrowser();

        $browser->request('GET', '/responders/template/');

        $response = $browser->getResponse();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('Hello World', $response->getContent());
    }
}
