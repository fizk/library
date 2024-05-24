<?php

namespace Library\Http\ServerFilter;

use Laminas\Diactoros\ServerRequestFilter\FilterServerRequestInterface;
use Psr\Http\Message\ServerRequestInterface;

class ServerFilterAggregate implements FilterServerRequestInterface
{
    public function __construct(/* FilterServerRequestInterface[] */ private $filters)
    {
    }

    public function __invoke(ServerRequestInterface $request): ServerRequestInterface
    {
        return array_reduce(
            $this->filters,
            fn (
            ServerRequestInterface $request,
            FilterServerRequestInterface $filter) => $filter($request),
            $request
        );
    }
}
