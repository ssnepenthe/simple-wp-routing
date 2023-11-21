<?php

declare(strict_types=1);

namespace SimpleWpRouting\Tests\Browser;

class OverlappingRulesTest extends TestCase
{
    public function testRoutingByMethodWhenRegexIsSame()
    {
        $browser = $this->getBrowser();

        $browser->request('GET', $this->testUri('/overlap/one/'));

        $response = $browser->getResponse();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame([
            'success' => true,
            'data' => 'GET overlap/one',
        ], json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR));

        $browser->request('POST', $this->testUri('/overlap/one/'));

        $response = $browser->getResponse();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame([
            'success' => true,
            'data' => 'POST overlap/one',
        ], json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR));

        $browser->request('PUT', $this->testUri('/overlap/one/'));

        $response = $browser->getResponse();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame([
            'success' => true,
            'data' => 'PUT overlap/one',
        ], json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR));
    }
}
