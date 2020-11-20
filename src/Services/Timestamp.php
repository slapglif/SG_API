<?php

namespace App\Services;

use DateTime;

class Timestamp
{
    public function reverseTransform($timestamp)
    {
        return (new DateTime())->setTimestamp($timestamp);
    }

    public function transform($dateTime)
    {
        if ($dateTime === null) {
            return (new DateTime('now'))->getTimestamp();
        }

        return $dateTime->getTimestamp();
    }

}