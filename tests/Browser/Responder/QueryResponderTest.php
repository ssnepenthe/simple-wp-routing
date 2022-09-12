<?php

declare(strict_types=1);

namespace ToyWpRouting\Tests\Browser\Responder;

use ToyWpRouting\Tests\Browser\TestCase;

class QueryResponderTest extends TestCase
{
    public function testQueryVariablesOnly()
    {
        $browser = $this->getBrowser();

        $crawler = $browser->request('GET', '/responders/query/');

        $response = $browser->getResponse();

        $queryVariables = json_decode(
            $crawler->filter('.query-responder-dump')->text(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('from-the-query-route', $queryVariables['custom-query-variable']);
    }
}
