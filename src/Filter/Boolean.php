<?php

declare(strict_types=1);

namespace Library\Filter;

use Laminas\Filter\FilterInterface;

class Boolean implements FilterInterface
{
    /**
     * @param  mixed $value
     * @return int|mixed
     */
    public function filter(mixed $value): mixed
    {
        if (is_numeric($value)) {
            return (bool) $value;
        }

        if (is_string($value)) {
            return match (strtolower($value)) {
                'true',
                'on',
                '1' => true,
                'false',
                'off',
                '0' => false,
                default => $value
            };
        }

        return $value;
    }

    public function __invoke(mixed $value): mixed
    {
        return $this->filter($value);
    }
}
