<?php

namespace Library\Router;

use Psr\Http\Message\ServerRequestInterface;

interface RouterInterface
{
    /**
     * Find a match in a router list.
     * 
     * Accepts a Request and uses the \Psr\Http\Message\UriInterface
     * to match against patterns defined in a router list.
     * 
     * @param ServerRequestInterface $request 
     * @return null|RouterMatch 
     */
    public function match(ServerRequestInterface $request): ?RouterMatch;
}
