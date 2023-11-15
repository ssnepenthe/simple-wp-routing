<?php

declare(strict_types=1);

namespace SimpleWpRouting\Tests\Browser;

use PHPUnit\Framework\TestCase as FrameworkTestCase;
use Symfony\Component\BrowserKit\AbstractBrowser;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpClient\Exception\TransportException;

class TestCase extends FrameworkTestCase
{
    protected static string $testHost = '';

    protected static function getTestHost(): string
    {
        if ('' === static::$testHost) {
            $url = file_get_contents(__DIR__ . '/test-url');
            $url = trim($url);

            $parsed = parse_url($url);

            static::$testHost = "{$parsed['host']}:{$parsed['port']}";
        }

        return static::$testHost;
    }

    protected function setUp(): void
    {
        try {
            $testData = $this->getBrowser()
                ->request('GET', '/')
                ->filter('.twr-test-data');

            if (! $testData->count()) {
                $this->markTestSkipped('The test plugin does not appear to be active');
            }
        } catch (TransportException $e) {
            $this->markTestSkipped($e->getMessage());
        }
    }

    protected function getBrowser(): AbstractBrowser
    {
        $browser = new HttpBrowser();
        $browser->setServerParameter('HTTP_HOST', static::getTestHost());
        $browser->followRedirects(false);

        return $browser;
    }

    protected function testUri(string $uri, array $query = []): string
    {
        if ($this->shouldUseRewriteCache()) {
            $query['twr_enable_cache'] = '1';
        }

        // @todo Some sort of output indicating that we are using cache?
        return [] === $query ? $uri : $uri . '?' . http_build_query($query);
    }

    protected function shouldUseRewriteCache(): bool
    {
        if (in_array('--use-rewrite-cache', $_SERVER['argv'], true)) {
            return true;
        }

        return filter_var(getenv('USE_REWRITE_CACHE'), FILTER_VALIDATE_BOOLEAN);
    }
}
