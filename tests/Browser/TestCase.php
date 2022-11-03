<?php

declare(strict_types=1);

namespace ToyWpRouting\Tests\Browser;

use PHPUnit\Framework\TestCase as FrameworkTestCase;
use Symfony\Component\BrowserKit\AbstractBrowser;
use Symfony\Component\BrowserKit\HttpBrowser;

class TestCase extends FrameworkTestCase
{
    protected function setUp(): void
    {
        $testData = $this->getBrowser()
            ->request('GET', '/')
            ->filter('.twr-test-data');

        if (! $testData->count()) {
            $this->markTestSkipped('The test plugin does not appear to be active');
        }
    }

    protected function getBrowser(): AbstractBrowser
    {
        $browser = new HttpBrowser();
        $browser->setServerParameter('HTTP_HOST', 'one.wordpress.test');
        $browser->followRedirects(false);

        return $browser;
    }

    protected function testUri(string $uri): string
    {
        // @todo Some sort of output indicating that we are using cache?
        return $this->shouldUseRewriteCache() ? "{$uri}?twr_enable_cache=1" : $uri;
    }

    protected function shouldUseRewriteCache(): bool
    {
        if (in_array('--use-rewrite-cache', $_SERVER['argv'], true)) {
            return true;
        }

        return filter_var(getenv('USE_REWRITE_CACHE'), FILTER_VALIDATE_BOOL);
    }
}
