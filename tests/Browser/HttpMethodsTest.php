<?php

declare(strict_types=1);

namespace ToyWpRouting\Tests\Browser;

class HttpMethodsTest extends TestCase
{
    private array $supportedMethods = ['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'];

    /** @dataProvider provideRequestMethodTestData */
    public function testRequestMethod($uri, $allowedMethods)
    {
        $unallowedMethods = array_diff($this->supportedMethods, $allowedMethods);
        $browser = $this->getBrowser();

        foreach ($allowedMethods as $method) {
            $browser->request($method, $uri);

            $this->assertSame(200, $browser->getResponse()->getStatusCode());
        }

        foreach ($unallowedMethods as $method) {
            $browser->request($method, $uri);

            $this->assertSame(405, $browser->getResponse()->getStatusCode());
        }
    }

    /** @dataProvider provideRequestMethodTestData */
    public function testRequestMethodOverride($uri, $allowedMethods)
    {
        $unallowedMethods = array_diff($this->supportedMethods, $allowedMethods);
        $browser = $this->getBrowser();

        foreach ($allowedMethods as $method) {
            $browser->request('POST', $uri, [], [], [
                'HTTP_X_HTTP_METHOD_OVERRIDE' => $method,
            ]);

            $this->assertSame(200, $browser->getResponse()->getStatusCode());
        }

        foreach ($unallowedMethods as $method) {
            $browser->request('POST', $uri, [], [], [
                'HTTP_X_HTTP_METHOD_OVERRIDE' => $method
            ]);

            $this->assertSame(405, $browser->getResponse()->getStatusCode());
        }
    }

    public function provideRequestMethodTestData()
    {
        yield ['/http-method/any/', $this->supportedMethods];
        yield ['/http-method/delete/', ['DELETE']];
        yield ['/http-method/get/', ['GET', 'HEAD']];
        yield ['/http-method/options/', ['OPTIONS']];
        yield ['/http-method/patch/', ['PATCH']];
        yield ['/http-method/post/', ['POST']];
        yield ['/http-method/put/', ['PUT']];
    }
}
