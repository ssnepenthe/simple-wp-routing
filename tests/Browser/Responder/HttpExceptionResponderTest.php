<?php

declare(strict_types=1);

namespace SimpleWpRouting\Tests\Browser\Responder;

use SimpleWpRouting\Tests\Browser\TestCase;

class HttpExceptionResponderTest extends TestCase
{
    public function testMethodNotAllowedException()
    {
        $browser = $this->getBrowser();

        $browser->request('GET', $this->testUri('/responders/http-exception/method-not-allowed/'));

        $response = $browser->getResponse();

        $this->assertSame(405, $response->getStatusCode());
        $this->assertSame('POST, PUT', $response->getHeader('allow'));
        $this->assertSame('Wed, 11 Jan 1984 05:00:00 GMT', $response->getHeader('expires'));
        $this->assertSame(
            'no-cache, must-revalidate, max-age=0',
            $response->getHeader('cache-control')
        );
    }

    public function testNotFoundException()
    {
        $browser = $this->getBrowser();

        $browser->request('GET', $this->testUri('/responders/http-exception/not-found/'));

        $response = $browser->getResponse();

        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame('Wed, 11 Jan 1984 05:00:00 GMT', $response->getHeader('expires'));
        $this->assertSame(
            'no-cache, must-revalidate, max-age=0',
            $response->getHeader('cache-control')
        );
    }
}
