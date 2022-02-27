<?php

namespace ToyWpRouting\Tests;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use ToyWpRouting\RequestContext;

class RequestContextTest extends TestCase
{
    public function testGetHeader()
    {
        $request = new RequestContext('GET', ['Apples' => 'Bananas']);

        $this->assertSame('Bananas', $request->getHeader('Apples'));
    }

    public function testGetHeaders()
    {
        $headers = [
            'A_KEY' => 'A_VALUE',
            'B_KEY' => 'b_value',
            'c_key' => 'C_VALUE',
            'd_key' => 'd_value'
        ];
        $request = new RequestContext('GET', $headers);

        // Key is lowercased, underscores replaced with dashes.
        // Value is unchanged.
        $this->assertSame([
            'a-key' => 'A_VALUE',
            'b-key' => 'b_value',
            'c-key' => 'C_VALUE',
            'd-key' => 'd_value',
        ], $request->getHeaders());
    }

    public function testGetIntendedMethod()
    {
        $headers = ['X-HTTP-METHOD-OVERRIDE' => 'PUT'];
        $allMethodsExceptPost = [
            'GET',
            'HEAD',
            'PUT',
            'DELETE',
            'CONNECT',
            'OPTIONS',
            'PATCH',
            'PURGE',
            'TRACE',
        ];

        // Always returns real method for non-POST requests, even for "invalid" methods.
        foreach (['INVALID', ...$allMethodsExceptPost] as $method) {
            $request = new RequestContext($method, $headers);

            $this->assertSame($method, $request->getIntendedMethod());
        }

        // POST request without override also returns real method.
        $request = new RequestContext('POST', []);
        $this->assertSame('POST', $request->getIntendedMethod());

        // POST requests with override return method from override header if valid.
        foreach (['POST', ...$allMethodsExceptPost] as $method) {
            $request = new RequestContext('POST', ['X-HTTP-METHOD-OVERRIDE' => $method]);

            $this->assertSame($method, $request->getIntendedMethod());
        }

        // Overrides are always uppercased.
        $request = new RequestContext('POST', ['X-HTTP-METHOD-OVERRIDE' => 'put']);

        $this->assertSame('PUT', $request->getIntendedMethod());
    }

    public function testGetIntendedMethodWithInvalidOverride()
    {
        $this->expectException(RuntimeException::class);

        $request = new RequestContext('POST', ['X-HTTP-METHOD-OVERRIDE' => 'INVALID']);

        $request->getIntendedMethod();
    }

    public function testGetMethod()
    {
        $request = new RequestContext('GET', []);

        $this->assertSame('GET', $request->getMethod());

        // Method is always uppercase.
        $request = new RequestContext('get', []);

        $this->assertSame('GET', $request->getMethod());
    }

    public function testExtractHeaders()
    {
        $server = [
            // Non-header values discarded.
            'SOME_VARIABLE' => 'some-value',

            // HTTP_ prefix stripped.
            'HTTP_APPLE' => 'banana',
            'HTTP_ZEBRA' => 'yak',

            // Untouched.
            'CONTENT_TYPE' => 'text/html',
            'CONTENT_LENGTH' => '0',
            'CONTENT_MD5' => 'abcdef',

            // Casing unmodified.
            'Http_Casing' => 'Looks_Weird',
        ];

        $this->assertSame([
            'APPLE' => 'banana',
            'ZEBRA' => 'yak',
            'CONTENT_TYPE' => 'text/html',
            'CONTENT_LENGTH' => '0',
            'CONTENT_MD5' => 'abcdef',
            'Casing' => 'Looks_Weird',
        ], RequestContext::extractHeaders($server));
    }
}
