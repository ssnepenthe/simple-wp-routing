<?php

declare(strict_types=1);

namespace SimpleWpRouting\Tests\Browser;

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

        $this->assertArrayHasKey('^(?|orchestrator/active/([^/]+))$', $rewrites);
        $this->assertArrayHasKey('^(?|orchestrator/inactive/([^/]+))$', $rewrites);
    }
}
