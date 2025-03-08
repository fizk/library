<?php

namespace Library\Router;

class RouterMatch implements RouterMatchInterface
{
    public function __construct(private mixed $value, private array $attributes, private string $pattern)
    {
    }

    public function getValue(): mixed
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
