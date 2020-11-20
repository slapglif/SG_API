<?php

namespace App\Controller\Site;

use App\Entity\CheckPoint;
use App\Entity\CheckpointInteraction;
use App\Entity\Site;
use App\Form\CheckpointInteractionType;
use App\Form\CheckPointType;
use App\Repository\CheckpointInteractionRepository;
use App\Services\CheckpointHelper;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Swagger\Annotations as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/")
 * @IsGranted({"ROLE_GUARD"})
 */
class CheckPointController extends AbstractController
{
    /**
     * @SWG\Get(
     *     tags={"Checkpoint"},
     *     @SWG\Parameter(
     *         name="site",
     *         required=true,
     *         in="path",
     *         type="integer",
     *         description="site id",
     *         @SWG\Schema(),
     *     ),
     *     @SWG\Response(
     *         response=202,
     *         description="Returns all checkpoints for chosen site.",
     *         @SWG\Schema(ref=@Model(type=CheckPoint::class))
     *         )
     * )
     * @Route("site/{site}/checkpoints", name="check_point_index", methods={"GET"})
     * @param SerializerInterface $serializer
     * @param Site $site
     * @return Response
     */
    public function index(SerializerInterface $serializer, Site $site): Response
    {
        $checkpoints = $site->getCheckPoints();

        $data = $serializer->serialize($checkpoints, 'json', SerializationContext::create()->enableMaxDepthChecks());

        return new JsonResponse($data, Response::HTTP_ACCEPTED, [], true);
    }

    /**
     * @SWG\Post(
     *     tags={"Checkpoint"},
     *     @SWG\Parameter(
     *         name="site",
     *         in="path",
     *         type="integer",
     *         description="Create a new site checkpoint.",
     *         @SWG\Schema()
     *     ),
     *     @SWG\Parameter(
     *         name="Checkpoint items",
     *         description="Items needed for checkpoint creation.",
     *         required=true,
     *         in="body",
     *         type="array",
     *         @SWG\Schema(
     *              @SWG\Property(property="site_id",type="integer",description="Id of the site you wish to add the checkpoint too."),
     *              @SWG\Property(property="name",type="string",description="Name of the asset eg entrance."),
     *              @SWG\Property(property="asset_id",type="string",description="Asset ID, example 04:DC:F6:A2:C1:66:80 (tags UUID)"),
     *              @SWG\Property(property="location_information",type="blob",description="Location Information (Detailed description of the tags location."),
     *              @SWG\Property(property="latitude",type="decimal",description="Latitude coordinates."),
     *              @SWG\Property(property="longitude",type="decimal",description="Longitude coordinates."),
     *              @SWG\Property(property="active",type="boolean",description="Is the tag is active."),
     *         )
     *     ),
     *     @SWG\Response(
     *         response=202,
     *         description="Returns created checkpoint",
     *         @SWG\Schema(ref=@Model(type=CheckPoint::class))
     *     )
     * )
     * @Route("site/{site}/checkpoint", name="check_point_new", methods={"POST"})
     * @IsGranted("ROLE_ADMIN")
     * @param Request $request
     * @param SerializerInterface $serializer
     * @return Response
     */
    public function new(Request $request, SerializerInterface $serializer): Response
    {
        $checkPoint = new CheckPoint();
        $form = $this->createForm(CheckPointType::class, $checkPoint);
        $form->submit(json_decode($request->getContent(), true));

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($checkPoint);
            $entityManager->flush();

            $data = $serializer->serialize($checkPoint, 'json', SerializationContext::create()->enableMaxDepthChecks());

            return new JsonResponse($data, Response::HTTP_ACCEPTED, [], true);
        }

        $error = [
            'error' => 'form validation or submission error',
            'error_object' => $form->getErrors(),
        ];

        $data = $serializer->serialize($error, 'json', SerializationContext::create()->enableMaxDepthChecks());

        return new JsonResponse($data, Response::HTTP_CONFLICT, [], true);
    }

    /**
     * @SWG\Get(
     *     tags={"Checkpoint"},
     *     @SWG\Response(
     *         response=202,
     *         description="Returns checkpoint for chosen site.",
     *         @SWG\Schema(ref=@Model(type=CheckPoint::class))
     *     )
     * )
     * @Route("checkpoint/{checkPoint}", name="check_point_show", methods={"GET"})
     * @param CheckPoint $checkPoint
     * @param SerializertowerInterface $serializer
     * @return Response
     */
    public function show(CheckPoint $checkPoint, SerializerInterface $serializer): Response
    {
        $data = $serializer->serialize($checkPoint, 'json', SerializationContext::create()->enableMaxDepthChecks());

        return new JsonResponse($data, Response::HTTP_ACCEPTED, [], true);
    }

    /**
     * @SWG\Post(
     *     tags={"Checkpoint"},
     *     @SWG\Parameter(
     *         name="site",
     *         in="path",
     *         type="integer",
     *         description="Edit a site checkpoint.",
     *         @SWG\Schema()
     *     ),
     *     @SWG\Parameter(
     *         name="Checkpoint items",
     *         description="Items needed for editing checkpoints.",
     *         required=true,
     *         in="body",
     *         type="array",
     *         @SWG\Schema(
     *              @SWG\Property(property="site",type="integer",description="Id of the site you wish to add the checkpoint too."),
     *              @SWG\Property(property="name",type="string",description="Name of the asset eg entrance."),
     *              @SWG\Property(property="assetId",type="string",description="Asset ID, example 04:DC:F6:A2:C1:66:80 (tags UUID)"),
     *              @SWG\Property(property="locationInformation",type="text",description="Location Information (Detailed description of the tags location."),
     *              @SWG\Property(property="latitude",type="decimal",description="Latitude coordinates."),
     *              @SWG\Property(property="longitude",type="decimal",description="Longitude coordinates."),
     *              @SWG\Property(property="active",type="boolean",description="Is the tag is active."),
     *         )
     *     ),
     *     @SWG\Response(
     *         response=202,
     *         description="Returns created checkpoint",
     *         @SWG\Schema(ref=@Model(type=CheckPoint::class))
     *     )
     * )
     * @Route("checkpoint/{checkPoint}", name="check_point_edit", methods={"POST"})
     * @param Request $request
     * @param CheckPoint $checkPoint
     * @param SerializerInterface $serializer
     * @return Response
     */
    public function edit(Request $request, CheckPoint $checkPoint, SerializerInterface $serializer): Response
    {
        $form = $this->createForm(CheckPointType::class, $checkPoint);
        $form->submit(json_decode($request->getContent(), true));

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $data = $serializer->serialize($checkPoint, 'json', SerializationContext::create()->enableMaxDepthChecks());

            return new JsonResponse($data, Response::HTTP_ACCEPTED, [], true);
        }

        $error = [
            'error' => 'form validation or submission error',
            'error_object' => $form->getErrors(),
        ];

        $data = $serializer->serialize($error, 'json', SerializationContext::create()->enableMaxDepthChecks());

        return new JsonResponse($data, Response::HTTP_CONFLICT, [], true);
    }

    /**
     * @SWG\Delete(
     *     tags={"Checkpoint"},
     *     @SWG\Response(
     *         response=200,
     *         description="Returns the checkpoints for the site",
     *         @SWG\Schema()
     *     )
     * )
     * @Route("checkpoint/{id}", name="check_point_delete", methods={"DELETE"})
     * @param CheckPoint $checkPoint
     * @param SerializerInterface $serializer
     * @return Response
     */
    public function delete(CheckPoint $checkPoint, SerializerInterface $serializer): Response
    {
        $site = $checkPoint->getSite();

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($checkPoint);
        $entityManager->flush();

        $data = $serializer->serialize(
            $site->getCheckPoints(),
            'json',
            SerializationContext::create()->enableMaxDepthChecks()
        );

        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    /**
     * @SWG\Post(
     *     tags={"checkpoint-interactions"},
     *     @SWG\Parameter(
     *         name="Asses ID",
     *         description="The assets UUID",
     *         in="path",
     *         type="string",
     *         @SWG\Schema(),
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Responds 200 if submitted, requests asset id",
     *         @SWG\Schema()
     *      )
     * )
     * @Route("checkpoint/submit/{assetId}", methods={"POST"})
     * @param $assetId
     * @param CheckpointHelper $checkpointHelper
     * @param Request $request
     * @param SerializerInterface $serializer
     * @IsGranted("ROLE_GUARD")
     * @return JsonResponse
     */
    public function checkpointAction(
        $assetId,
        CheckpointHelper $checkpointHelper,
        Request $request,
        SerializerInterface $serializer
    ) {
        $checkpointInteraction = new CheckpointInteraction();
        $checkpoint = $checkpointHelper->checkpointFinder($assetId);
        $form = $this->createForm(CheckpointInteractionType::class, $checkpointInteraction);
        $form->submit(json_decode($request->getContent(), true));

        if ($form->isSubmitted() && $form->isValid()) {
            $check = $checkpointHelper->checkpointTimeValidation(
                $checkpointInteraction->submitInfo($checkpoint, $this->getUser()),
                $this->getUser(),
                $checkpoint
            );

            if ($check == 1) {
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($checkpointInteraction);
                $entityManager->flush();

                $data = $serializer->serialize(
                    $checkpointInteraction,
                    'json',
                    SerializationContext::create()->enableMaxDepthChecks()
                );

                return new JsonResponse($data, Response::HTTP_ACCEPTED, [], true);
            }
            $data = $serializer->serialize($check, 'json', SerializationContext::create()->enableMaxDepthChecks());

            return new JsonResponse($data, Response::HTTP_BAD_REQUEST, [], true);
        }

        $error = [
            'error' => 'form validation or submission error',
            'error_object' => $form->getErrors(),
        ];

        $data = $serializer->serialize($error, 'json', SerializationContext::create()->enableMaxDepthChecks());

        return new JsonResponse($data, Response::HTTP_CONFLICT, [], true);
    }

    /**
     * @SWG\Get(
     *     tags={"checkpoint-interactions"},
     *     @SWG\Parameter(
     *          name="Site Id",
     *          description="The sites uuid value",
     *          in="path",
     *          type="integer",
     *          @SWG\Schema(),
     *     ),
     *     @SWG\Response(
     *          response=200,
     *          description="Returns the last 50 submitted checkpoint interactions for given site.",
     *          @SWG\Schema(),
     *     )
     * )
     * @Route("site/{site}/checkpoint-interactions", methods={"GET"})
     * @param Site $site
     * @param SerializerInterface $serializer
     * @param CheckpointInteractionRepository $checkpointInteractionRepository
     * @return JsonResponse
     */
    public function siteCheckpointShow(
        Site $site,
        SerializerInterface $serializer,
        CheckpointInteractionRepository $checkpointInteractionRepository
    ) {
        $checkpoints = $site->getCheckPoints();

        foreach ($checkpoints as $checkpoint) {
            $checkpointIds[] = $checkpoint->getId();
        }

        $something = $checkpointInteractionRepository->findBy(
            array('checkpoint' => $checkpointIds),
            array('submitted' => 'DESC'),
            50,
            null
        );

        $data = $serializer->serialize($something, 'json', SerializationContext::create()->enableMaxDepthChecks());

        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    /**
     * @SWG\Get(
     *     tags={"checkpoint-interactions"},
     *     @SWG\Parameter(
     *          name="Checkpoint",
     *          in="path",
     *          description="Checkpoint UUID value.",
     *          type="string",
     *          @SWG\Schema(),
     *      ),
     *     @SWG\Response(
     *          response=200,
     *          description="Checkpoints last submitted actions (50 Limit).",
     *          @SWG\Schema(),
     *     )
     * )
     * @Route("checkpoint-interactions/{checkpoint}", )
     * @param SerializerInterface $serializer
     * @param CheckPoint $checkpoint
     * @param CheckpointInteractionRepository $checkpointInteractionRepository
     * @return JsonResponse
     */
    public function checkpointShow(
        SerializerInterface $serializer,
        $checkpoint,
        CheckpointInteractionRepository $checkpointInteractionRepository
    ) {
        $info = $checkpointInteractionRepository->findBy(
            array('checkpoint' => $checkpoint),
            array('submitted' => 'DESC'),
            50,
            null
        );

        $data = $serializer->serialize($info, 'json', SerializationContext::create()->enableMaxDepthChecks());

        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }
}
