<?php

namespace Library\Dependency;

use Psr\EventDispatcher\EventDispatcherInterface;

trait DispatchableAwareTrait
{
    private EventDispatcherInterface $dispatchable;

    public function getDispatchable(): EventDispatcherInterface
    {
        return $this->dispatchable;
    }

    public function setDispatchable(EventDispatcherInterface $dispachable): static
    {
        $this->dispatchable = $dispachable;
        return $this;
    }
}
