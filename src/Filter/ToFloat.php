<?php

declare(strict_types=1);

namespace Library\Filter;

use Laminas\Filter\FilterInterface;

class ToFloat implements FilterInterface
{
    /**
     * @param  mixed $value
     * @return int|mixed
     */
    public function filter(mixed $value): mixed
    {
        if (!is_numeric($value)) {
            return null;
        }

        return (float) $value;
    }

    public function __invoke(mixed $value): mixed
    {
        return $this->filter($value);
    }
}
