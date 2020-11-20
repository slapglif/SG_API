<?php

namespace App\Services;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use DateTime;

class GuardHelper
{
    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(
        UserRepository $userRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->userRepository = $userRepository;
        $this->em = $entityManager;
    }

    /**
     * @param $userId
     * @param $siteId
     * @param $rangeStart
     * @param $rangeEnd
     * @return string
     */
    public function getGuardEarningAmount($userId, $siteId, $rangeStart, $rangeEnd)
    {
        $user = $this->userRepository->findOneBy(['id' => $userId]);

        $data = array(
            'guard' => array(
                'id' => $user->getId(),
                'first_name' => $user->getFirstName(),
                'last_name' => $user->getLastName(),
                'default_pay_rate' => $user->getDefaultPayRate(),
                'total_earning_amount' => 0,
                'total_day_earning_amount' => 0,
                'total_night_earning_amount' => 0
            ),
            'shift' => []
        );

        $rawQuery = "
            SELECT 
                report.id as user_id, report.first_name, report.last_name, 
                report.site_id, report.site_name, report.day_shift_start_time, report.night_shift_start_time, report.late_limit, 
                report.shift_start, report.shift_end,
                report.actual_shift_start, report.actual_shift_end,
                report.day_rate, report.night_rate,
                report.shift_hours, report.actual_shift_hours,
                report.approved
            FROM
            (
                SELECT 
                    u.id, u.first_name, u.last_name, s.name as site_name, s.id as site_id,
                    s.day_shift_start_time, s.night_shift_start_time, s.late_limit,
                    DATE_FORMAT(g.shift_start, '%Y-%m-%d %H:%i:%s') as shift_start,
                    DATE_FORMAT(g.shift_end, '%Y-%m-%d %H:%i:%s') as shift_end,
                    DATE_FORMAT(g.actual_shift_start, '%Y-%m-%d %H:%i:%s') as actual_shift_start,
                    DATE_FORMAT(g.actual_shift_end, '%Y-%m-%d %H:%i:%s') as actual_shift_end,
                    IF (s.day_rate > 0, s.day_rate, u.default_pay_rate) AS day_rate,
                    IF (s.night_rate > 0, s.night_rate, u.default_pay_rate) AS night_rate,
                    ((unix_timestamp(g.shift_start) - unix_timestamp(g.actual_shift_start)) / 60) as late_minute,
                    ((unix_timestamp(g.actual_shift_end) - unix_timestamp(g.shift_end)) / 60) as over_minute,
                    ((unix_timestamp(g.shift_end) - unix_timestamp(g.shift_start)) / 3600) as shift_hours,
                    ((unix_timestamp(g.actual_shift_end) - unix_timestamp(g.actual_shift_start)) / 3600) as actual_shift_hours,
                    g.approved
                FROM guard_shift g 
                    LEFT JOIN user u ON g.user_id = u.id 
                    LEFT JOIN site s ON g.site_id = s.id 
                WHERE TRUE
                AND u.id = '$userId'
            ) as report
            WHERE TRUE
                AND (report.late_minute <= report.late_limit OR report.approved = 1)
                AND (report.over_minute - report.late_minute) >= 0
        ";

        if ($siteId > 0 ) {
            $rawQuery .= " AND report.site_id = '$siteId'";
        }
        if ($rangeStart) {
            $rawQuery .= " AND DATEDIFF(report.shift_start, '" . $rangeStart->format('Y-m-d H:i:s') . "') >= 0";
        }
        if ($rangeEnd) {
            $rawQuery .= " AND DATEDIFF(report.shift_end, '" . $rangeEnd->format('Y-m-d H:i:s') . "') <= 0";
        }

        $statement = $this->em->getConnection()->prepare($rawQuery);
        $statement->execute();

        $result = $statement->fetchAll();

        $day_earning = 0;
        $night_earning = 0;

        foreach ($result as $key => $shift) {
            $hours = $this->getDayAndNightShiftHours(
                $shift['approved'] == 1 ? $shift['actual_shift_start'] : $shift['shift_start'], 
                $shift['approved'] == 1 ? $shift['actual_shift_end'] : $shift['shift_end'], 
                $shift['day_shift_start_time'],
                $shift['night_shift_start_time']
            );

            $s = array (
                'site_id' => $shift['site_id'],
                'site_name' => $shift['site_name'],
                'shift_start' => $shift['shift_start'],
                'shift_end' => $shift['shift_end'],
                'total_hours' => round($hours['total'], 2),
                'total_earning' => round($shift['day_rate'] *  $hours['day'], 2) + round($shift['night_rate'] *  $hours['night'], 2),
                'day_rate' => $shift['day_rate'],
                'day_hours' => round($hours['day'], 2),
                'day_earning' =>  round($shift['day_rate'] *  $hours['day'], 2),
                'night_rate' => $shift['night_rate'],
                'night_hours' => round($hours['night'], 2),
                'night_earning' =>  round($shift['night_rate'] *  $hours['night'], 2),
            );
            $data['shift'][] = $s;

            $day_earning += round($shift['day_rate'] * $hours['day'], 2);
            $night_earning += round($shift['night_rate'] * $hours['night'], 2);
        }

        $data['guard']['total_earning_amount'] = round($day_earning + $night_earning, 2);
        $data['guard']['total_day_earning_amount'] = round($day_earning, 2);
        $data['guard']['total_night_earning_amount'] = round($night_earning, 2);

        return json_encode($data);
    }

    /**
     * @param $shiftStart
     * @param $shiftEnd
     * @param $dayShiftStartTime
     * @param $nightShiftStartTime
     * @return Array
     */
    public function getDayAndNightShiftHours($shiftStart, $shiftEnd, $dayShiftStartTime, $nightShiftStartTime)
    {
        /*
         * Calc how many hours in dayShift and nightShift (2000-01-01: no meaning)
         */
        $dayHoursPerDay = 0;
        $nightHoursPerDay = 0;
        $dayShiftStartTimeSecond = strtotime("2000-01-01 $dayShiftStartTime:00");
        $nightShiftStartTimeSecond = strtotime("2000-01-01 $nightShiftStartTime:00");

        if ($nightShiftStartTimeSecond > $dayShiftStartTimeSecond) {
            $dayHoursPerDay = ($nightShiftStartTimeSecond - $dayShiftStartTimeSecond) / 3600;
            $nightHoursPerDay = 24 - $dayHoursPerDay;
        } else if ($nightShiftStartTimeSecond < $dayShiftStartTimeSecond) {
            $nightHoursPerDay = ($dayShiftStartTimeSecond - $nightShiftStartTimeSecond) / 3600;
            $dayHoursPerDay = 24 - $nightHoursPerDay;
        } else {
            $dayHoursPerDay = 24;
            $nightHoursPerDay = 0;
        }
        

        // ----------------------------------------------------
        $totalDayShiftHours = 0;
        $totalNightShiftHours = 0;
        // ----------------------------------------------------

        $shiftStartDateTime = new DateTime($shiftStart);
        $shiftStartSecond = $shiftStartDateTime->getTimestamp();

        $shiftEndDateTime = new DateTime($shiftEnd);
        $shiftEndSecond = $shiftEndDateTime->getTimestamp();

        if (($shiftStartSecond % 86400) < ($shiftEndSecond % 86400)) {
            /**
             * e.g: Y-m-d 08:00:00 ~ Y-m-d 13:00:00
             */
            $endDate = $shiftEndDateTime->format('Y-m-d');
            $endTime = $shiftStartDateTime->format('H:i:s');

            $newEndDate = new DateTime("$endDate $endTime");

            // Calc total days first
            $totalDayShiftHours = (($newEndDate->getTimestamp() - $shiftStartSecond) / 86400) * $dayHoursPerDay;
            $totalNightShiftHours = (($newEndDate->getTimestamp() - $shiftStartSecond) / 86400) * $nightHoursPerDay;

            $endDateDayShiftStart = new DateTime("$endDate $dayShiftStartTime:00");
            $endDateNightShiftStart = new DateTime("$endDate $nightShiftStartTime:00");

            if ($endDateDayShiftStart->getTimestamp() <= $newEndDate->getTimestamp()) {
                // e.g: dayShiftStart: 8 am, newEndDate: 9 am
                if ($endDateNightShiftStart->getTimestamp() <= $shiftEndSecond) {
                    // e.g: nightShiftStart: 8 pm, shiftEnd: 9 pm
                    $totalDayShiftHours += ($endDateNightShiftStart->getTimestamp() - $newEndDate->getTimestamp()) / 3600;
                    $totalNightShiftHours += ($shiftEndSecond - $endDateNightShiftStart->getTimestamp()) / 3600;
                } else {
                    // e.g: nighShiftStart: 10 pm, shiftEnd: 9 pm
                    $totalDayShiftHours += ($shiftEndSecond - $newEndDate->getTimestamp()) / 3600;
                }
            } else {
                // e.g: dayShiftStart: 8 am, newEndDate: 5 am
                $totalNightShiftHours += ($endDateDayShiftStart->getTimestamp() - $newEndDate->getTimestamp()) / 3600;
                if ($endDateNightShiftStart->getTimestamp() <= $shiftEndSecond) {
                    // e.g: nightShiftStart: 8 pm, shiftEnd: 9 pm
                    $totalDayShiftHours += $dayHoursPerDay;
                    $totalNightShiftHours += ($shiftEndSecond - $endDateNightShiftStart->getTimestamp()) / 3600;
                } else {
                    // e.g: nighShiftStart: 10 pm, shiftEnd: 9 pm
                    $totalDayShiftHours += ($shiftEndSecond - $endDateDayShiftStart->getTimestamp()) / 3600;
                }
            }
        } else if (($shiftStartSecond % 86400) > ($shiftEndSecond % 86400)){
            /**
             * e.g: Y-m-d 08:00:00 ~ Y-m-d 07:00:00
             */
            $startDate = $shiftStartDateTime->format('Y-m-d');
            $startTime = $shiftEndDateTime->format('H:i:s');

            $newStartDate = new DateTime("$startDate $startTime");

            // Calc total days first
            $totalDayShiftHours = (($shiftEndSecond - $newStartDate->getTimestamp()) / 86400) * $dayHoursPerDay;
            $totalNightShiftHours = (($shiftEndSecond - $newStartDate->getTimestamp()) / 86400) * $nightHoursPerDay;

            $startDateDayShiftStart = new DateTime("$startDate $dayShiftStartTime:00");
            $startDateNightShiftStart = new DateTime("$startDate $nightShiftStartTime:00");

            if ($newStartDate->getTimestamp() <= $startDateDayShiftStart->getTimestamp()) {
                // e.g: dayShiftStart: 8 am, newStartDate: 7 am
                if ($shiftStartSecond <= $startDateDayShiftStart->getTimestamp()) {
                    // e.g: dayShiftStart: 8 am, shifStart: 7:30 am
                    $totalNightShiftHours -= ($shiftStartSecond - $newStartDate->getTimestamp()) / 3600;
                } else {
                    // e.g: dayShiftStart: 10 pm, shifStart: 7:30 am
                    $totalNightShiftHours -= ($startDateDayShiftStart->getTimestamp() - $newStartDate->getTimestamp()) / 3600;
                    $totalDayShiftHours -= ($shiftStartSecond - $startDateDayShiftStart->getTimestamp()) / 3600;
                }
            } else {
                // e.g: dayShiftStart: 8 am, newStartDate: 9 am,
                $totalDayShiftHours -= ($newStartDate->getTimestamp() - $startDateDayShiftStart->getTimestamp()) / 3600;

                if ($startDateNightShiftStart->getTimestamp() <= $shiftStartSecond) {
                    // e.g: nightShiftStart: 7 pm, shifStart: 7:30 pm
                    $totalDayShiftHours -= ($startDateNightShiftStart->getTimestamp() - $newStartDate->getTimestamp()) / 3600;
                    $totalNightShiftHours -= ($shiftStartSecond - $startDateNightShiftStart->getTimestamp()) / 3600;
                } else {
                    // e.g: nighShiftStart: 10 pm, shiftStart: 9 pm
                    $totalDayShiftHours -= ($shiftStartSecond - $newStartDate->getTimestamp()) / 3600;
                }
            }
        } else {
            /**
             * e.g: Y-m-d 08:00:00 - Y-m-d 08:00:00
             */
            $totalDay = ($shiftEndSecond - $shiftStartSecond) / 86400;
            $totalDayShiftHours = $totalDay * $dayHoursPerDay;
            $totalNightShiftHours = $totalDay * $nightHoursPerDay;
        }

        return array(
            'total' => $totalDayShiftHours + $totalNightShiftHours,
            'day' => $totalDayShiftHours,
            'night' => $totalNightShiftHours
        );
    }
}