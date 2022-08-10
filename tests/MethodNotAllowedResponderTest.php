<?php

declare(strict_types=1);

namespace ToyWpRouting\Tests;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use ToyWpRouting\Responder\MethodNotAllowedResponder;

use function Brain\Monkey\Functions\expect;
use function Brain\Monkey\setUp;
use function Brain\Monkey\tearDown;

class MethodNotAllowedResponderTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    protected function setUp(): void
    {
        parent::setUp();
        setUp();
    }

    protected function tearDown(): void
    {
        tearDown();
        parent::tearDown();
    }

    public function testOnBodyClass()
    {
        $responder = new MethodNotAllowedResponder(['GET', 'POST']);

        // Non-array input returned unmodified.
        $this->assertSame(5, $responder->onBodyClass(5));

        // Array input gets 'error405' pushed on before return.
        $this->assertSame(['some-class', 'error405'], $responder->onBodyClass(['some-class']));
    }

    public function testOnDocumentTitleParts()
    {
        $responder = new MethodNotAllowedResponder(['GET', 'POST']);

        // Non-array input returned unmodified.
        $this->assertSame(5, $responder->onDocumentTitleParts(5));

        // Array input gets a new 'title' before return.
        $this->assertSame(
            ['title' => 'Method not allowed'],
            $responder->onDocumentTitleParts(['title' => 'Irrelevant title'])
        );
        $this->assertSame(
            ['title' => 'Method not allowed', 'tagline' => 'Just Another WordPress Site'],
            $responder->onDocumentTitleParts(
                ['title' => 'Irrelevant title', 'tagline' => 'Just Another WordPress Site']
            )
        );
    }

    public function testOnParseQuery()
    {
        $wpQuery = Mockery::spy('WP_Query');
        $responder = new MethodNotAllowedResponder(['GET', 'POST']);

        $responder->onParseQuery($wpQuery);

        $wpQuery->shouldHaveReceived()->init_query_flags();
    }

    public function testOnTemplateInclude()
    {
        expect('status_header')
            ->once()
            ->with(405);

        expect('nocache_headers')
            ->once()
            ->withNoArgs();

        $fakeTemplatePath = '/some/path/to/405.php';

        expect('get_query_template')
            ->once()
            ->with('405')
            ->andReturn($fakeTemplatePath);

        $responder = new MethodNotAllowedResponder(['GET', 'POST']);

        $this->assertSame($fakeTemplatePath, $responder->onTemplateInclude('irrelevant'));
    }

    public function testOnTemplateIncludeTemplateNotFound()
    {
        expect('status_header')
            ->once();

        expect('nocache_headers')
            ->once();

        expect('get_query_template')
            ->once()
            ->andReturn('');

        $responder = new MethodNotAllowedResponder(['GET', 'POST']);

        $this->assertSame(
            dirname(__DIR__) . '/templates/405.php',
            $responder->onTemplateInclude('irrelevant')
        );
    }

    public function testOnWpHeaders()
    {
        $responder = new MethodNotAllowedResponder(['GET', 'POST']);

        // Non-array input returned unmodified.
        $this->assertSame(5, $responder->onWpHeaders(5));

        // Array input gets a new 'Allow' before return.
        $this->assertSame(['Allow' => 'GET, POST'], $responder->onWpHeaders(['Allow' => 'PUT']));
        $this->assertSame(
            ['Allow' => 'GET, POST', 'Server' => 'nginx'],
            $responder->onWpHeaders(['Allow' => 'PUT', 'Server' => 'nginx'])
        );
    }

    public function testOnWpHeadersMethodsAreCapitalized()
    {
        $responder = new MethodNotAllowedResponder(['get', 'post']);

        $this->assertSame(['Allow' => 'GET, POST'], $responder->onWpHeaders(['Allow' => 'PUT']));
    }

    public function testRespond()
    {
        $responder = new MethodNotAllowedResponder(['GET', 'POST']);
        $responder->respond();

        $fqcn = MethodNotAllowedResponder::class;

        $this->assertNotFalse(has_filter('body_class', "{$fqcn}->onBodyClass()"));
        $this->assertNotFalse(has_filter('document_title_parts', "{$fqcn}->onDocumentTitleParts()"));
        $this->assertNotFalse(has_action('parse_query', "{$fqcn}->onParseQuery()"));
        $this->assertNotFalse(has_filter('template_include', "{$fqcn}->onTemplateInclude()"));
        $this->assertNotFalse(has_filter('wp_headers', "{$fqcn}->onWpHeaders()"));
    }
}
