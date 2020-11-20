<?php

namespace App\Form;

use DateTime;
use Symfony\Component\Form\DataTransformerInterface;

class TimestampToDateTimeTransformer implements DataTransformerInterface
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
