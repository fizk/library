<?php

namespace Library\Dependency;

use Psr\EventDispatcher\EventDispatcherInterface;

interface DispatchableAwareInterface
{
    public function getDispatchable(): EventDispatcherInterface;

    public function setDispatchable(EventDispatcherInterface $dispachable): static;
}
