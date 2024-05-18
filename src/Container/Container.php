<?php

namespace Library\Container;

use Library\Container\Exception\InvalidDefinitionException;
use Library\Container\Exception\NotFoundException;
use Library\Container\Exception\AlreadyDefinedException;
use Psr\Container\ContainerInterface;

class Container implements ContainerInterface
{
    private array $config = [];
    private array $cache = [];

    public function __construct(array $config = [])
    {
        $this->config = array_reduce(
            $config,
            fn ($previous, $current) => [...$previous, ...$current],
            []
        );
    }

    public function get(string $id)
    {
        // is the dependency in cache
        if (array_key_exists($id, $this->cache)) {
            return $this->cache[$id];
        }

        // is the dependency definition absent from config
        if ($this->has($id) === false) {
            throw new NotFoundException("Definition for $id not found");
        }

        // is the dependency definition `callable`
        if (is_callable($this->config[$id])) {
            return $this->cache[$id] = $this->config[$id]($this);
        }

        throw new InvalidDefinitionException("Definition for $id is not callable");
    }

    public function has(string $id): bool
    {
        return array_key_exists($id, $this->config);
    }

    public function add(string $id, callable $definition): self
    {
        if ($this->has($id)) {
            throw new AlreadyDefinedException("Definition for $id aready defined");
        }
        $this->config[$id] = $definition;
        return $this;
    }

    public function remove(string $id): self
    {
        unset($this->config[$id]);
        unset($this->cache[$id]);

        return $this;
    }

    public function override(string $id, callable $definition): self
    {
        if (! $this->has($id)) {
            throw new NotFoundException("Definition for $id not found");
        }

        unset($this->cache[$id]);
        $this->config[$id] = $definition;

        return $this;
    }
}
