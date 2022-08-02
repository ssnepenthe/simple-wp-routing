<?php

namespace ToyWpRouting\Tests\Responder;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use ToyWpRouting\Responder\HookDrivenResponder;

// @todo bring in brain/monkey
class HookDrivenResponderTest extends TestCase
{
    public function testInitializeTraits()
    {
        $responder = new class extends HookDrivenResponder
        {
            use One;
            use Two;
        };
        $responder->respond();

        $this->assertSame(1, $responder->oneCount);
        $this->assertSame(1, $responder->twoCount);
    }

    public function testCheckForConflicts()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('test-conflict-message');

        $responder = new class extends HookDrivenResponder
        {
            use Two;
            use Three;
        };
        $responder->respond();
    }
}

trait One
{
    public $oneCount = 0;

    protected function initializeOne(): void
    {
        $this->oneCount++;
    }
}

trait Two
{
    public $twoCount = 0;

    protected function initializeTwo(): void
    {
        $this->twoCount++;
    }
}

trait Three
{
    protected function initializeThree(): void
    {
        $this->addConflictCheck(function () {
            if (property_exists($this, 'twoCount')) {
                return 'test-conflict-message';
            }
        });
    }
}
