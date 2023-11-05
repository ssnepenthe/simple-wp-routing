<?php

declare(strict_types=1);

namespace ToyWpRouting\Tests\Browser;

class OrchestratorTest extends TestCase
{
    public function testHandlerReturnsHierarchicalResponder()
    {
        $browser = $this->getBrowser();
        $browser->request('GET', $this->testUri('/orchestrator/hierarchical-responder/'));

        $content = json_decode(
            $browser->getResponse()->getContent(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        $this->assertSame(200, $browser->getResponse()->getStatusCode());
        $this->assertSame([
            'success' => true,
            'data' => 'hello from the orchestrator hierarchical responder route',
        ], $content);
    }

    public function testHandlerReturnsResponder()
    {
        $browser = $this->getBrowser();
        $browser->request('GET', $this->testUri('/orchestrator/responder/'));

        $content = json_decode(
            $browser->getResponse()->getContent(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        $this->assertSame(200, $browser->getResponse()->getStatusCode());
        $this->assertSame([
            'success' => true,
            'data' => 'hello from the orchestrator responder route',
        ], $content);
    }

    public function testInactiveRouteHandlersAreNotCalled()
    {
        $browser = $this->getBrowser();
        $crawler = $browser->request('GET', $this->testUri('/orchestrator/inactive/irrelevant'));

        $this->assertSame(404, $browser->getResponse()->getStatusCode());
        $this->assertSame(0, $crawler->filter('.twr-orchestrator-inactive')->count());
    }

    public function testMethodNotAllowed()
    {
        $browser = $this->getBrowser();
        $browser->request('POST', $this->testUri('/orchestrator/active/irrelevant'));

        $this->assertSame(405, $browser->getResponse()->getStatusCode());
    }

    public function testMissingRequiredQueryVariables()
    {
        // In the event that a user bypasses "pretty permalinks" and uses query string directly
        // we want to return a 400 response if any required query variables are missing.
        $browser = $this->getBrowser();

        // Matches the orchestrator/active/{activeVar} route, missing activeVar query variable.
        $browser->request(
            'GET',
            $this->testUri('/', ['orchestrator_matchedRule' => '5cc12d9280457964a1502740d21f1321'])
        );

        $this->assertSame(400, $browser->getResponse()->getStatusCode());
    }

    public function testQueryVariablesAreMerged()
    {
        $browser = $this->getBrowser();
        $crawler = $browser->request('GET', $this->testUri('/'));
        $queryVars = json_decode(
            $crawler->filter('.twr-query-vars')->text(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        $this->assertContains('orchestrator_activeVar', $queryVars);
        $this->assertContains('orchestrator_inactiveVar', $queryVars);
    }

    public function testRewritesAreMerged()
    {
        $browser = $this->getBrowser();
        $crawler = $browser->request('GET', $this->testUri('/'));
        $rewrites = json_decode(
            $crawler->filter('.twr-rewrites')->text(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        $this->assertArrayHasKey('^orchestrator/active/([^/]+)$', $rewrites);
        $this->assertArrayHasKey('^orchestrator/inactive/([^/]+)$', $rewrites);
    }
}
