<?php

declare(strict_types=1);

namespace ToyWpRouting\Tests;

use Invoker\InvokerInterface;
use PHPUnit\Framework\TestCase;
use ToyWpRouting\InvokerBackedInvocationStrategy;
use ToyWpRouting\RewriteInterface;

// @todo Test with custom resolver set on Invoker instance?
// @todo Move additional parameter tests to dedicated abstract invocation strategy test.
class InvokerBackedInvocationStrategyTest extends TestCase
{
    use CreatesRewriteStubs;

    public function testInvokeHandler()
    {
        $invoker = $this->createMock(InvokerInterface::class);
        $invoker->expects($this->once())
            ->method('call')
            ->with('handler', [])
            ->willReturn('returnvalue');

        $strategy = new InvokerBackedInvocationStrategy($invoker);

        $rewrite = $this->createRewriteStub([
            'handler' => 'handler',
        ]);

        $this->assertSame('returnvalue', $strategy->invokeHandler($rewrite));
    }

    public function testInvokeHandlerWithAdditionalParameters()
    {
        $invoker = $this->createMock(InvokerInterface::class);
        $invoker->expects($this->once())
            ->method('call')
            ->with('handler', ['one' => 'testvalue'])
            ->willReturn('returnvalue');

        $rewrite = $this->createRewriteStub([
            'handler' => 'handler',
            'qvMap' => ['one' => 'one'],
        ]);

        $returnValue = (new InvokerBackedInvocationStrategy($invoker))
            ->withAdditionalContext(['queryVars' => ['one' => 'testvalue']])
            ->invokeHandler($rewrite);

        $this->assertSame('returnvalue', $returnValue);
    }

    public function testInvokeHandlerWithCallableResolver()
    {
        $invoker = $this->createMock(InvokerInterface::class);
        $invoker->expects($this->once())
            ->method('call')
            ->with('modifiedhandler', []);

        $strategy = new InvokerBackedInvocationStrategy($invoker);
        $strategy->setCallableResolver(function ($potentialCallable) {
            if ('handler' === $potentialCallable) {
                return 'modifiedhandler';
            }

            return $potentialCallable;
        });

        $rewrite = $this->createRewriteStub([
            'handler' => 'handler',
        ]);

        $strategy->invokeHandler($rewrite);
    }

    public function testInvokeHandlerWithPrefixedAdditionalParameters()
    {
        $invoker = $this->createMock(InvokerInterface::class);
        $invoker->expects($this->once())
            ->method('call')
            ->with('handler', ['one' => 'testvalue'])
            ->willReturn('returnvalue');

        $rewrite = $this->createRewriteStub([
            'handler' => 'handler',
            'qvMap' => ['pfx_one' => 'one'],
        ]);

        $returnValue  = (new InvokerBackedInvocationStrategy($invoker))
            ->withAdditionalContext(['queryVars' => ['pfx_one' => 'testvalue']])
            ->invokeHandler($rewrite);

        $this->assertSame('returnvalue', $returnValue);
    }

    public function testInvokeIsActiveCallback()
    {
        $invoker = $this->createMock(InvokerInterface::class);
        $invoker->expects($this->exactly(2))
            ->method('call')
            ->withConsecutive(['isactiveone'], ['isactivetwo'])
            ->willReturnOnConsecutiveCalls(true, false);

        $strategy = new InvokerBackedInvocationStrategy($invoker);

        $one = $this->createRewriteStub([
            'isActive' => 'isactiveone',
        ]);

        $two = $this->createRewriteStub([
            'isActive' => 'isactivetwo',
        ]);

        $this->assertTrue($strategy->invokeIsActiveCallback($one));
        $this->assertFalse($strategy->invokeIsActiveCallback($two));
    }

    public function testInvokeIsActiveCallbackWithCallableResolver()
    {
        $invoker = $this->createMock(InvokerInterface::class);
        $invoker->expects($this->once())
            ->method('call')
            ->with('modifiedisactive');

        $strategy = new InvokerBackedInvocationStrategy($invoker);
        $strategy->setCallableResolver(function ($potentialCallable) {
            if ('isactive' === $potentialCallable) {
                return 'modifiedisactive';
            }

            return $potentialCallable;
        });

        $rewrite = $this->createRewriteStub([
            'isActive' => 'isactive',
        ]);

        $strategy->invokeIsActiveCallback($rewrite);
    }

    public function testInvokeIsActiveCallbackWithNoCallbackSet()
    {
        $invoker = $this->createMock(InvokerInterface::class);
        $invoker->expects($this->never())
            ->method('call');

        $strategy = new InvokerBackedInvocationStrategy($invoker);

        $rewrite = $this->createRewriteStub([
            'isActive' => null,
        ]);

        $isActive = $strategy->invokeIsActiveCallback($rewrite);

        $this->assertTrue($isActive);
    }

    public function testInvokeIsActiveCallbackWithNonBooleanReturnValue()
    {
        $invoker = $this->createMock(InvokerInterface::class);

        $invoker->expects($this->exactly(2))
            ->method('call')
            ->withConsecutive(['isactiveone'], ['isactivetwo'])
            ->willReturnOnConsecutiveCalls(1, '');

        $strategy = new InvokerBackedInvocationStrategy($invoker);

        $one = $this->createRewriteStub([
            'isActive' => 'isactiveone',
        ]);

        $two = $this->createRewriteStub([
            'isActive' => 'isactivetwo',
        ]);

        $this->assertTrue($strategy->invokeIsActiveCallback($one));
        $this->assertFalse($strategy->invokeIsActiveCallback($two));
    }
}
