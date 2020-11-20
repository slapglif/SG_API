<?php

namespace App\Services;

use App\Entity\CheckPoint;
use App\Repository\CheckpointInteractionRepository;
use App\Repository\CheckPointRepository;
use DateInterval;
use DateTime;
use Exception;

class CheckpointHelper
{
    /**
     * @var CheckPointRepository
     */
    private $checkpointEntityManager;
    /**
     * @var CheckpointInteractionRepository
     */
    private $checkpointInteractionEntityManager;

    public function __construct(
        CheckPointRepository $checkpointEntityManager,
        CheckpointInteractionRepository $checkpointInteractionEntityManager
    ) {
        $this->checkpointEntityManager = $checkpointEntityManager;
        $this->checkpointInteractionEntityManager = $checkpointInteractionEntityManager;
    }

    /**
     * @param $assetID
     * @return CheckPoint|null
     */
    public function checkpointFinder($assetID)
    {
        return $this->checkpointEntityManager->findOneBy(array('assetId' => $assetID));
    }

    /**
     * @param $formData
     * @param $user
     * @param $checkpoint
     * @return int|string
     * @throws Exception
     */
    public function checkpointTimeValidation($formData, $user, $checkpoint)
    {
        $currTime = new DateTime();
        $checkpointId = $checkpoint->getId();
        $lastCheckpointInteraction = $this->checkpointInteractionEntityManager->findLatestInteraction($checkpointId);
        $lastCheckpointInteractionSubmitted = $lastCheckpointInteraction[0]->getSubmitted();
        $siteTapFrequency = $checkpoint->getSite()->getTapFrequency();
        $clockInFrom = $lastCheckpointInteractionSubmitted->add(new DateInterval('PT'.$siteTapFrequency.'M'));

        if ($currTime > $clockInFrom) {
            if ($user == $formData->getShift()->getUser()) {
                if ($currTime > $formData->getShift()->getShiftStart() && $currTime < $formData->getShift(
                    )->getShiftEnd()) {
                    return 1;
                } else {
                    return 'Outside shift hours';
                }
            } else {
                return 'Invalid user';
            }
        } else {
            $difference = $currTime->diff($clockInFrom);

            return 'Too early to register checkpoint, time remaining: '.$difference->format('%I Minutes %s Seconds');
        }
    }
}