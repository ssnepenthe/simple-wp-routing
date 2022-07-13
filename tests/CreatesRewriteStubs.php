<?php

namespace ToyWpRouting\Tests;

use ToyWpRouting\RewriteInterface;

trait CreatesRewriteStubs
{
    /**
     * @param array{handler?: mixed, isActive?: mixed, qvMap?: array} $args
     */
    private function createRewriteStub(array $args = [])
    {
        $rewrite = $this->createStub(RewriteInterface::class);

        if (array_key_exists('handler', $args)) {
            $rewrite->method('getHandler')->willReturn($args['handler']);
        }

        if (array_key_exists('isActive', $args)) {
            $rewrite->method('getIsActiveCallback')->willReturn($args['isActive']);
        }

        if (array_key_exists('qvMap', $args)) {
            $rewrite->method('getPrefixedToUnprefixedQueryVariablesMap')
                ->willReturn($args['qvMap']);
        }

        return $rewrite;
    }
}
