<?php

namespace App\Controller\User;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\GuardShiftRepository;
use App\Services\Timestamp;
use App\Services\GuardHelper;
use DateTime;
use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @IsGranted("ROLE_GUARD")
 * Class GuardController
 * @package App\Controller\User
 * @Route("/api/guard", name="app_guard_")
 */
class GuardController extends AbstractFOSRestController
{
    /**
     * @SWG\Get(
     *     tags={"Guard"},
     *     @SWG\Parameter(
     *         name="uuid",
     *         in="query",
     *         type="integer",
     *         description="Users uuid number",
     *         @SWG\Schema(
     *             @SWG\Property(property="site",type="integer",description="site uuid number."),
     *             @SWG\Property(property="rangeStart",type="string",description="Search start date."),
     *             @SWG\Property(property="rangeEnd",type="string",description="Search end date."),
     *         )
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Returns amount of earning",
     *     @SWG\Schema(ref=@Model(type=User::class))
     *     )
     * )
     * @Route("/{uuid}/earning", name="guard-earning", methods={"GET"})
     * @param $uuid
     * @param Request $request
     * @param Timestamp $timestamp,
     * @param GuardHelper $guardHelper
     * @return Response
     */
    public function guardEarning($uuid, Request $request, Timestamp $timestamp, GuardHelper $guardHelper)
    {
        $siteId = $request->get('site');
        $rangeStart = NULL;
        $rangeEnd = NULL;

        if ($request->get('rangeStart')) {
            $rangeStart = $timestamp->reverseTransform($request->get('rangeStart'));
        }
        if ($request->get('rangeEnd')) {
            $rangeEnd = $timestamp->reverseTransform($request->get('rangeEnd'));
        }

        $guardEarningData = $guardHelper->getGuardEarningAmount(
            $uuid, $siteId, $rangeStart, $rangeEnd);

        return new JsonResponse($guardEarningData, Response::HTTP_OK, [], true);
    }
}
