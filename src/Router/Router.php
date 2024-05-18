<?php

namespace Library\Router;

use Psr\Http\Message\ServerRequestInterface;

class Router
{
    private array $patterns;

    public function __construct(array $patterns = [])
    {
        $this->patterns = array_reduce(
            $patterns,
            fn ($previous, $current) => [...$previous, ...$current],
            []
        );
    }

    public function add(string $regex, string $value): self
    {
        $this->patterns[$regex] = $value;
        return $this;
    }

    public function remove(string $regex): self
    {
        unset($this->patterns[$regex]);
        return $this;
    }

    public function match(ServerRequestInterface $request): ?RouterMatch
    {
        foreach ($this->patterns as $pattern => $value) {
            if (preg_match($pattern, $request->getUri()->getPath(), $match)) {
                array_shift($match);
                return new RouterMatch($value, $match, $pattern);
            }
        }

        return null;
    }
}
