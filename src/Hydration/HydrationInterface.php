<?php

namespace Library\Hydration;

interface HydrationInterface
{
    public function hydrate(array $data, object $object);
}
