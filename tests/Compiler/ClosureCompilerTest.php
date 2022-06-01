<?php

declare(strict_types=1);

namespace ToyWpRouting\Tests\Compiler;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use ToyWpRouting\Compiler\ClosureCompiler;

class ClosureCompilerTest extends TestCase
{
    public function testCompileDoesntDuplicateStaticKeyword()
    {
        $this->assertStringStartsNotWith('static static', (new ClosureCompiler(static function () {
        }))->compile());
    }

    public function testCompileForcesStaticClosure()
    {
        $this->assertStringStartsWith('static', (new ClosureCompiler(function () {
        }))->compile());
    }

    public function testCompileWithParent()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/"parent"[a-z",\s]+not supported/');

        (new ClosureCompiler(function () {
            parent::irrelevant();
        }))->compile();
    }

    public function testCompileWithSelf()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/"self"[a-z",\s]+not supported/');

        (new ClosureCompiler(function () {
            self::irrelevant();
        }))->compile();
    }

    public function testCompileWithStatic()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/"static"[a-z",\s]+not supported/');

        (new ClosureCompiler(function () {
            static::irrelevant();
        }))->compile();
    }

    public function testCompileWithThis()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/"\$this"[a-z",\s]+not supported/');

        (new ClosureCompiler(function () {
            $this->irrelevant();
        }))->compile();
    }

    public function testCompileWithUse()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('"use" imports not supported');

        $var = 'irrelevant';

        (new ClosureCompiler(function () use ($var) {
        }))->compile();
    }
}
