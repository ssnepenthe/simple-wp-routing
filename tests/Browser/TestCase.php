<?php

namespace ToyWpRouting\Tests\Browser;

use PHPUnit\Framework\TestCase as FrameworkTestCase;
use Symfony\Component\BrowserKit\AbstractBrowser;
use Symfony\Component\BrowserKit\HttpBrowser;

class TestCase extends FrameworkTestCase
{
    protected function getBrowser(): AbstractBrowser
    {
        $browser = new HttpBrowser();
        $browser->setServerParameter('HTTP_HOST', 'one.wordpress.test');
        $browser->followRedirects(false);

        return $browser;
    }

    protected function setUp(): void
    {
        $testData = $this->getBrowser()
            ->request('GET', '/')
            ->filter('.twr-test-data');

        if (! $testData->count()) {
            $this->markTestSkipped('The test plugin does not appear to be active');
        }
    }
}
