<?php

namespace Library\Filter;

use DateMalformedStringException;
use DateTime;
use Laminas\Filter\FilterInterface;

class Date implements FilterInterface
{
    /**
     * Returns the result of filtering $value
     *
     * @template T
     * @param T $value
     * @return TFilteredValue|T
     * @throws Exception\RuntimeException If filtering $value is impossible.
     */
    public function filter(mixed $value): ?DateTime
    {
        if ($value === '' || $value === null) {
            return null;
        }

        if ($value instanceof DateTime) {
            return $value;
        }

        try {
            return new DateTime($value);
        } catch (DateMalformedStringException $exception) {
            return null;
        }
    }

    /**
     * Returns the result of filtering $value
     *
     * @template T
     * @param T $value
     * @return TFilteredValue|T
     * @throws Exception\RuntimeException If filtering $value is impossible.
     */
    public function __invoke(mixed $value): ?DateTime
    {
        return $this->filter($value);
    }
}
