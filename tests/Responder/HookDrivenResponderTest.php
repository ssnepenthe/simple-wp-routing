<?php

declare(strict_types=1);

namespace ToyWpRouting\Tests\Responder;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use ToyWpRouting\Responder\HookDrivenResponder;

// @todo bring in brain/monkey
class HookDrivenResponderTest extends TestCase
{
    public function testCheckForConflicts()
    {
        $responder = new class () extends HookDrivenResponder {
            use One;
            use Three;
        };
        $responder->respond();

        $this->assertSame(1, $responder->conflictCount);
    }

    public function testCheckForConflictsThrowsFirstConflict()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('test-conflict-message');

        $responder = new class () extends HookDrivenResponder {
            use Two;
            use Three;
        };
        $responder->respond();
    }

    public function testInitializeTraits()
    {
        $responder = new class () extends HookDrivenResponder {
            use One;
            use Two;
        };
        $responder->respond();

        $this->assertSame(1, $responder->oneCount);
        $this->assertSame(1, $responder->twoCount);
    }
}

trait One
{
    public $conflictCount = 0;
    public $oneCount = 0;

    protected function initializeOne(): void
    {
        $this->oneCount++;

        $this->addConflictCheck(function () {
            $this->conflictCount++;

            // As long as we don't return a string we shouldn't get any errors.
            return;
        });
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
