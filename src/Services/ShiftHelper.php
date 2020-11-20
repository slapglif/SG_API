<?php

namespace App\Services;

use App\Repository\GuardShiftRepository;
use DateTime;

class ShiftHelper
{
    /**
     * @var GuardShiftRepository
     */
    private $guardShiftRepository;
    /**
     * @var Timestamp
     */
    private $timestamp;

    public function __construct(GuardShiftRepository $guardShiftRepository, Timestamp $timestamp)
    {
        $this->guardShiftRepository = $guardShiftRepository;
        $this->timestamp = $timestamp;
    }

    /**
     * @param DateTime $shiftStart
     * @param $shiftEnd
     * @param $siteId
     * @param $userId
     * @return bool|int|null
     */
    public function isShiftClashing($shiftStart, $shiftEnd, $siteId, $userId)
    {
        $shiftFinder = $this->guardShiftRepository->findBy(
            [
                'user' => $userId->getId(),
                'site' => $siteId->getId(),
            ]
        );

        foreach ($shiftFinder as $key => $shift) {
            //Checking if the start of a shift is between an existing shift
            if ($shiftStart > $shift->getShiftStart() && $shiftStart < $shift->getShiftEnd()) {
                return $clashingShifts = $shift->getId();
            }
            //Checking if the end of a shift is between an existing shift
            if ($shiftEnd > $shift->getShiftStart() && $shiftEnd < $shift->getShiftEnd()) {
                return $clashingShifts = $shift->getId();
            }
            //Checking if the shift is a duplicate
            if ($shiftStart == $shift->getShiftStart() && $shiftEnd == $shift->getShiftEnd()) {
                return $clashingShifts = $shift->getId();
            }
        }

        return false;
    }

    public function upComingShift($userId)
    {
        $time = $this->timestamp->transform($this->date = new DateTime());

        $shiftFinder = $this->guardShiftRepository->findBy(
            [
                'user' => $userId,
            ]
        );

        foreach ($shiftFinder as $shift) {
            $interval[] = abs($time - $this->timestamp->transform($shift->getShiftStart()));
            $shiftId = $shift->getId();
        }
        asort($interval);
        $closest = key($interval);

        return ($shiftFinder[$closest]);
    }

    public function canClockIn($shiftId)
    {
        $shiftFinder = $this->guardShiftRepository->findoneBy(
            [
                'id' => $shiftId,
            ]
        );

        $currentTime = $this->timestamp->transform($this->date = new DateTime());
        $oneHourBefore = $this->timestamp->transform($shiftFinder->getShiftStart()) - 3600;
        $oneHourAfter = $this->timestamp->transform($shiftFinder->getShiftStart()) + 3600;

        if ($currentTime >= $oneHourBefore && $currentTime <= $oneHourAfter) {
            return true;
        } else {
            return false;
        }
    }
}