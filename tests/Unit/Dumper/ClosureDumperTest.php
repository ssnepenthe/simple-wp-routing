<?php

declare(strict_types=1);

namespace SimpleWpRouting\Tests\Unit\Dumper;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use SimpleWpRouting\Dumper\ClosureDumper;

class ClosureDumperTest extends TestCase
{
    public function testDumpDoesntDuplicateStaticKeyword()
    {
        $this->assertStringStartsNotWith('static static', (new ClosureDumper(static function () {
        }))->dump());
    }

    public function testDumpForcesStaticClosure()
    {
        $this->assertStringStartsWith('static', (new ClosureDumper(function () {
        }))->dump());
    }

    public function testDumpWithParent()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/"parent"[a-z",\s]+not supported/');

        (new ClosureDumper(function () {
            parent::irrelevant();
        }))->dump();
    }

    public function testDumpWithSelf()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/"self"[a-z",\s]+not supported/');

        (new ClosureDumper(function () {
            self::irrelevant();
        }))->dump();
    }

    public function testDumpWithStatic()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/"static"[a-z",\s]+not supported/');

        (new ClosureDumper(function () {
            static::irrelevant();
        }))->dump();
    }

    public function testDumpWithThis()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/"\$this"[a-z",\s]+not supported/');

        (new ClosureDumper(function () {
            $this->irrelevant();
        }))->dump();
    }

    public function testDumpWithUse()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('"use" imports not supported');

        $var = 'irrelevant';

        (new ClosureDumper(function () use ($var) {
        }))->dump();
    }
}
