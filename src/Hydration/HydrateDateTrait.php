<?php

namespace Lib\Hydration;

use DateTime;

trait HydrateDateTrait
{
    private function hydrateDate($date)
    {
        if (is_null($date)) {
            return null;
        }

        if (is_string($date)) {
            return new DateTime($date);
        }

        if ($date instanceof DateTime) {
            return $date;
        }
    }
}
