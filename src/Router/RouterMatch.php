<?php

namespace Library\Router;

class RouterMatch
{
    public function __construct(private string $value, private array $attributes, private string $pattern)
    {
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getPattern(): string
    {
        return $this->pattern;
    }
}
