<?php

namespace Library\Http\ServerFilter;

use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\ServerRequestFilter\FilterServerRequestInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

class ServerFilterAggregateTest extends TestCase
{
    public function testSetHeaderAndThenOverrideItInAnotherFilter()
    {
        $filter1 = new class implements FilterServerRequestInterface {
            public function __invoke(ServerRequestInterface $request): ServerRequestInterface
            {
                return $request->withMethod('POST')
                    ->withHeader('content-type', 'undefined');
            }
        };

        $filter2 = new class implements FilterServerRequestInterface {
            public function __invoke(ServerRequestInterface $request): ServerRequestInterface
            {
                return $request->withHeader('content-type', 'text/plain');
            }
        };

        $aggregate = new ServerFilterAggregate([$filter1, $filter2]);

        $filteredRequest = $aggregate(new ServerRequest());

        $this->assertEquals('POST', $filteredRequest->getMethod());
        $this->assertEquals(['text/plain'], $filteredRequest->getHeader('content-type'));
    }
}
