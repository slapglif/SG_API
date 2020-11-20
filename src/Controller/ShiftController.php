<?php

namespace App\Controller;

use App\Entity\GuardShift;
use App\Entity\Site;
use App\Entity\User;
use App\Form\GuardShiftType;
use App\Repository\GuardShiftRepository;
use App\Services\GeoFenceHelper;
use App\Services\ShiftHelper;
use App\Services\Timestamp;
use DateTime;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Location\Coordinate;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/shift")
 */
class ShiftController extends AbstractFOSRestController
{
    /**
     * @SWG\Post(
     *     tags={"Shifts"},
     *     @SWG\Parameter(
     *         name="Form body details.",
     *         in="body",
     *         type="form",
     *         @SWG\Schema(ref=@Model(type="GuardShiftType:class"))
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Returns selected users information",
     *     @SWG\Schema(ref=@Model(type=GuardShift::class))
     *     ),
     * )
     * @Route("/create-shift", name="create-shift", methods={"POST"})
     * @IsGranted("ROLE_ADMIN")
     * @param Request $request
     * @param ShiftHelper $shiftClash
     * @param SerializerInterface $serializer
     * @return Response
     */
    public function createShift(Request $request, ShiftHelper $shiftClash, SerializerInterface $serializer)
    {
        $shift = new GuardShift();
        $shift->setAdmin($this->getUser());
        $form = $this->createForm(GuardShiftType::class, $shift);
        $form->submit(json_decode($request->getContent(), true));

        if ($form->isSubmitted() && $form->isValid()) {
            $clashingId = $shiftClash->isShiftClashing(
                $form->get('shift_start')->getData(),
                $form->get('shift_end')->getData(),
                $form->get('site')->getData(),
                $form->get('user')->getData()
            );

            if ($clashingId == true) {
                $clash = $this->getDoctrine()->getRepository(GuardShift::class)->findOneBy(['id' => $clashingId]);
                $data = $serializer->serialize($clash, 'json', SerializationContext::create()->enableMaxDepthChecks());

                return new JsonResponse($data, Response::HTTP_CONFLICT, [], true);
            }
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($form->getData());
            $entityManager->flush();

            $data = $serializer->serialize($shift, 'json', SerializationContext::create()->enableMaxDepthChecks());

            return new JsonResponse($data, Response::HTTP_OK, [], true);
        }
        $error = [
            'error' => 'form validation or submission error',
            'error_object' => $form->getErrors(),
        ];

        $data = $serializer->serialize($error, 'json', SerializationContext::create()->enableMaxDepthChecks());

        return new JsonResponse($data, Response::HTTP_BAD_REQUEST, [], true);
    }

    /**
     * @SWG\Put(
     *     tags={"Shifts"},
     *     @SWG\Parameter(
     *         name="Body Details.",
     *         in="body",
     *         required=true,
     *         type="array",
     *         description="Querys needed for editing a guards shift.",
     *         @SWG\Schema(
     *             @SWG\Property(property="user",type="string",description="Users uuid number."),
     *             @SWG\Property(property="site", type="integer",description="Sites uuid number."),
     *             @SWG\Property(property="shift_start", type="Datetime",description="The starting time of the desired shift."),
     *             @SWG\Property(property="shift_end", type="Datetime",description="the finish time of the desired shift."),
     *             @SWG\Property(property="actual_shift_start", type="Datetime",description="The start time of the guard for the shift."),
     *             @SWG\Property(property="actual_shift_end", type="Datetime",description="The end time of the guard for the shift."),
     *         )
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Shift will be returned",
     *         @SWG\Schema(ref=@Model(type=GuardShift::class))
     *     )
     * )
     * @Route("/{shift}", name="edit-shift", methods={"PUT"})
     * @IsGranted("ROLE_ADMIN")
     * @param Request $request
     * @param GuardShift $shift
     * @param SerializerInterface $serializer
     * @return Response
     */
    public function editShift(Request $request, GuardShift $shift, SerializerInterface $serializer)
    {
        $actualShiftStart = $request->request->get('actual_shift_start');
        $actualShiftEnd = $request->request->get('actual_shift_end');
        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(['id' => $request->request->get('user')]);
        $site = $this->getDoctrine()->getRepository(Site::class)->findOneBy(['id' => $request->request->get('site')]);
        $shift->setUser($user);
        $shift->setSite($site);
        $shift->setShiftStart(new DateTime($request->request->get('shift_start')));
        $shift->setShiftEnd(new DateTime($request->request->get('shift_end')));

        if ($actualShiftStart != null) {
            $shift->setActualShiftStart($actualShiftStart);
            $shift->setActualShiftEnd($actualShiftEnd);
        }
        $this->getDoctrine()->getManager()->flush();
        $shiftObject = $serializer->serialize($shift, 'json', SerializationContext::create()->enableMaxDepthChecks());

        return new JsonResponse($shiftObject, Response::HTTP_OK, [], true);
    }

    /**
     * @SWG\Delete(
     *     tags={"Shifts"},
     *     @SWG\Parameter(
     *         name="shiftId",
     *         in="path",
     *         required=true,
     *         type="integer",
     *         description="shift uuid number.",
     *         @SWG\Schema(
     *         )
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Shift will be returned",
     *         @SWG\Schema(ref=@Model(type=GuardShift::class))
     *     )
     * )
     * @Route("/{shift}", name="delete-shift", methods={"DELETE"})
     * @IsGranted("ROLE_ADMIN")
     * @param GuardShift $shift
     * @param SerializerInterface $serializer
     * @return Response
     */
    public function deleteShift(GuardShift $shift, SerializerInterface $serializer)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($shift);
        $entityManager->flush();

        $data = $serializer->serialize($shift, 'json', SerializationContext::create());

        return new JsonResponse($data, Response::HTTP_ACCEPTED, [], true);
    }

    /**
     * @SWG\Get(
     *     tags={"Shifts"},
     *     @SWG\Parameter(
     *         name="Query details.",
     *         in="body",
     *         required=true,
     *         type="array",
     *         description="Querys for requesting a shift details.",
     *         @SWG\Schema(
     *             @SWG\Property(property="user",type="string",description="Users uuid number."),
     *             @SWG\Property(property="site", type="integer",description="Sites uuid number."),
     *             @SWG\Property(property="admin", type="integer",description="Admins uuid number."),
     *             @SWG\Property(property="shift_start", type="Datetime",description="The starting time of the desired shift."),
     *             @SWG\Property(property="shift_end", type="Datetime",description="the finish time of the desired shift."),
     *             @SWG\Property(property="page", type="integer",description="Page query."),
     *             @SWG\Property(property="limit", type="integer",description="Limit the number of responses."),
     *         )
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Shift will be returned",
     *         @SWG\Schema(ref=@Model(type=GuardShift::class))
     *     )
     * )
     * @Route("/all", name="all-shifts", methods={"GET"})
     * @IsGranted("ROLE_GUARD")
     * @param Request $request
     * @param PaginatorInterface $paginator
     * @param Timestamp $timestamp
     * @param GuardShiftRepository $guardShiftRepository
     * @return Response
     */
    public function shifts(
        Request $request,
        PaginatorInterface $paginator,
        Timestamp $timestamp,
        GuardShiftRepository $guardShiftRepository
    ) {
        $site = $request->get('site');
        $user = $request->get('user');
        $admin = $request->get('admin');
        $rangeStart = $timestamp->reverseTransform($request->get('rangeStart'));
        $rangeEnd = $timestamp->reverseTransform($request->get('rangeEnd'));

        $allShiftsQuery = $guardShiftRepository->shiftFinder($site, $user, $admin, $rangeStart, $rangeEnd);

        $shifts = $paginator->paginate(
            $allShiftsQuery,
            $request->query->getInt('page', 1),
            $request->query->getInt('limit', 10)
        );

        return $this->handleView($this->view($shifts));
    }

    /**
     * @SWG\Get(
     *     tags={"Shifts"},
     *     description="Returns all guards with the 'ROLE_GUARD'",
     *     @SWG\Response(
     *         response=200,
     *         description="User model",
     *         @SWG\Schema(ref=@Model(type=User::class))
     *     )
     * )
     * @Route("/active-guards", name="active-guards", methods={"GET"})
     * @IsGranted("ROLE_SITE_ADMIN")
     * @param SerializerInterface $serializer
     * @return string
     */
    public function activeGuards(SerializerInterface $serializer)
    {
        $data = $this->getDoctrine()->getRepository(User::class)->findActiveGuards('ROLE_GUARD');
        $shift = $serializer->serialize($data, 'json', SerializationContext::create()->enableMaxDepthChecks());

        return new JsonResponse($shift, 200, [], true);
    }

    /**
     * @SWG\Post(
     *     tags={"Shifts"},
     *     @SWG\Parameter(
     *         name="Guard Location",
     *         in="body",
     *         required=true,
     *         type="array",
     *         description="Querys for requesting a shift details. A guard can only clock in for their shifts an hour ether side of the shift. ",
     *         @SWG\Schema(
     *             @SWG\Property(property="latitude",type="float",description="Guards current latitude."),
     *             @SWG\Property(property="longitude", type="float",description="Guards current longitude."),
     *         )
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="",
     *         @SWG\Schema()
     *     )
     * )
     * @IsGranted("ROLE_GUARD")
     * @Route("/clock-in/{shift}", name="specific-clock-in", methods={"POST"})
     * @param ShiftHelper $shiftHelper
     * @param GuardShift $shift
     * @param GeoFenceHelper $geoFenceHelper
     * @param Request $request
     * @return Response
     */
    public function specificShiftClockingIn(
        ShiftHelper $shiftHelper,
        GuardShift $shift,
        GeoFenceHelper $geoFenceHelper,
        Request $request
    ) {
        if ($this->getUser()->getRoles() == ['ROLE_GUARD'] && $this->getUser() === $shift->getUser()) {
            $currentLocation = new Coordinate($request->request->get('latitude'), $request->request->get('longitude'));
            $canClockIn = $shiftHelper->canClockIn($shift);
            if ($geoFenceHelper->perimeterCheck($shift->getSite(), $currentLocation)) {
                $onSite = true;
            } else {
                return $this->handleView($this->view("Guard is not on site."));
            }
        } else {
            $canClockIn = true;
            $onSite = true;
        }

        if ($canClockIn === true) {
            if ($shift->getActualShiftStart() != null && $onSite === true) {
                return $this->handleView($this->view("This shift has already commenced."));
            }
            $this->getDoctrine()->getManager()->persist($shift->setActualShiftStart($this->date = new DateTime()));
            $this->getDoctrine()->getManager()->flush();

            return $this->handleView($this->view("You have been clocked in for your shift."));
        } else {
            return $this->handleView($this->view("No shift has been found within 1 hour."));
        }
    }

    /**
     * @SWG\Get(
     *     tags={"Shifts"},
     *     @SWG\Response(
     *         response=200,
     *         description="",
     *         @SWG\Schema()
     *     )
     * )
     * @IsGranted("ROLE_GUARD")
     * @Route("/clock-out/{shift}", name="clock-out", methods={"GET"})
     * @param $shift
     * @return Response
     */
    public function shiftClockingOut($shift)
    {

        if ($this->getUser()->getRoles() == ['ROLE_GUARD']) {
            $shiftObject = $this->getDoctrine()->getRepository(GuardShift::class)->findOneBy(['id' => $shift]);

            $this->getDoctrine()->getManager()->persist($shiftObject->setActualShiftEnd($this->date = new DateTime()));
            $this->getDoctrine()->getManager()->flush();

            return $this->handleView($this->view("You have been clocked out for your shift."));
        }

    }

    /**
     * @SWG\Get(
     *     tags={"Shifts"},
     *     @SWG\Parameter(
     *         name="Return single shift information.",
     *         in="path",
     *         required=true,
     *         type="integer",
     *         description="Shifts uuid number.",
     *         @SWG\Schema()
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Shift model",
     *         @SWG\Schema()
     *     )
     * )
     * @Route("/{shift}", name="shift-display", methods={"GET"})
     * @IsGranted("ROLE_GUARD")
     * @param $shift
     * @param SerializerInterface $serializer
     * @return string
     */
    public function shiftDisplay($shift, SerializerInterface $serializer)
    {
        $data = $this->getDoctrine()->getRepository(GuardShift::class)->findOneBy(['id' => $shift]);
        $shiftObject = $serializer->serialize($data, 'json', SerializationContext::create()->enableMaxDepthChecks());

        return new JsonResponse($shiftObject, 200, [], true);
    }

    /**
     * @SWG\Put(
     *     tags={"Shifts"},
     *     @SWG\Parameter(
     *         name="shiftId",
     *         in="path",
     *         required=true,
     *         type="integer",
     *         description="shift uuid number.",
     *         @SWG\Schema(
     *         )
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Shift will be returned",
     *         @SWG\Schema(ref=@Model(type=GuardShift::class))
     *     )
     * )
     * @Route("/{shift}/approve", name="approve-shift", methods={"PUT"})
     * @IsGranted("ROLE_ADMIN")
     * @param GuardShift $shift
     * @param SerializerInterface $serializer
     * @return Response
     */
    public function approveShift(GuardShift $shift, SerializerInterface $serializer)
    {
        $entityManager = $this->getDoctrine()->getManager();
        
        $shift->setApproved(1);
        
        $entityManager->flush();

        $data = $serializer->serialize($shift, 'json', SerializationContext::create());

        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    /**
     * @SWG\Put(
     *     tags={"Shifts"},
     *     @SWG\Parameter(
     *         name="shiftId",
     *         in="path",
     *         required=true,
     *         type="integer",
     *         description="shift uuid number.",
     *         @SWG\Schema(
     *         )
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Shift will be returned",
     *         @SWG\Schema(ref=@Model(type=GuardShift::class))
     *     )
     * )
     * @Route("/{shift}/decline", name="decline-shift", methods={"PUT"})
     * @IsGranted("ROLE_ADMIN")
     * @param GuardShift $shift
     * @param SerializerInterface $serializer
     * @return Response
     */
    public function declineShift(GuardShift $shift, SerializerInterface $serializer)
    {
        $entityManager = $this->getDoctrine()->getManager();
        
        $shift->setApproved(0);
        
        $entityManager->flush();

        $data = $serializer->serialize($shift, 'json', SerializationContext::create());

        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }
}
