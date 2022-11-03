<?php

declare(strict_types=1);

namespace ToyWpRouting\Tests\Browser\Responder;

use ToyWpRouting\Tests\Browser\TestCase;

class JsonResponderTest extends TestCase
{
    public function testDataOnly()
    {
        $browser = $this->getBrowser();

        $browser->request('GET', $this->testUri('/responders/json/'));

        $response = $browser->getResponse();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame([
            'success' => true,
            'data' => 'hello from the json responder route',
        ], json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR));
    }
}
