<?php

declare(strict_types=1);

namespace ToyWpRouting\Tests\Browser\Responder;

use ToyWpRouting\Tests\Browser\TestCase;

class RedirectResponderTest extends TestCase
{
    public function testLocationOnly()
    {
        $browser = $this->getBrowser();

        $crawler = $browser->request('GET', $this->testUri('/responders/redirect/'));

        $this->assertSame(302, $browser->getResponse()->getStatusCode());
        $this->assertSame('/responders/redirect/', parse_url($crawler->getUri(), PHP_URL_PATH));

        $crawler = $browser->followRedirect();

        $this->assertSame(200, $browser->getResponse()->getStatusCode());
        $this->assertSame('/responders/query/', parse_url($crawler->getUri(), PHP_URL_PATH));
    }
}
