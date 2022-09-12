<?php

declare(strict_types=1);

namespace ToyWpRouting\Tests\Browser\Responder;

use ToyWpRouting\Tests\Browser\TestCase;

class RedirectResponderTest extends TestCase
{
    public function testLocationOnly()
    {
        $browser = $this->getBrowser();

        $crawler = $browser->request('GET', '/responders/redirect/');

        $this->assertSame(302, $browser->getResponse()->getStatusCode());
        $this->assertStringEndsWith('/responders/redirect/', $crawler->getUri());

        $crawler = $browser->followRedirect();

        $this->assertSame(200, $browser->getResponse()->getStatusCode());
        $this->assertStringEndsWith('/responders/query/', $crawler->getUri());
    }
}
