<?php

namespace App\Controller\Site;

use App\Entity\Perimeter;
use App\Form\PerimeterType;
use App\Repository\PerimeterRepository;
use App\Services\GeoFenceHelper;
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
 * @Route("/api/sites/perimeter")
 * @IsGranted("ROLE_GUARD")
 */
class PerimeterController extends AbstractFOSRestController
{
    /**
     * @SWG\Get(
     *     tags={"Perimeter"},
     *     @SWG\Parameter(
     *         name="siteId",
     *         in="path",
     *         type="integer",
     *         required=true,
     *         @SWG\Schema()
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="sites perimeter",
     *         @SWG\Schema(ref=@Model(type=Perimeter::class))
     *     )
     * )
     * @Route("/{siteId}", name="perimeter_show", methods={"GET"})
     * @param PerimeterRepository $perimeterRepository
     * @param $siteId
     * @return Response
     */
    public function index(PerimeterRepository $perimeterRepository, $siteId): Response
    {
        $siteInfo = $perimeterRepository->findBy(['site' => $siteId]);

        return $this->handleView($this->view($siteInfo));
    }

    /**
     * @SWG\Post(
     *     tags={"Perimeter"},
     *     @SWG\Parameter(
     *         name="Body Details",
     *         in="body",
     *         required=true,
     *         type="array",
     *         description="Items needed for creating a new perimeter point for a site.",
     *         @SWG\Schema(
     *             @SWG\Property(property="refNumber",type="integer",description="Refrence number showing what point the user is interacting with."),
     *             @SWG\Property(property="latitude", type="texttype",description=""),
     *             @SWG\Property(property="longitude", type="texttype",description=""),
     *             @SWG\Property(property="site", type="integer",description="Site UUID."),
     *         )
     *     ),
     *     @SWG\Response(
     *         response=201,
     *         description="Return created perimeter.",
     *         @SWG\Schema(ref=@Model(type=Perimeter::class))
     *     )
     * )
     * @Route("/new", name="perimeter_new", methods={"POST"})
     * @param Request $request
     * @param SerializerInterface $serializer
     * @return Response
     * @IsGranted("ROLE_SITE_ADMIN")
     */
    public function new(Request $request, SerializerInterface $serializer): Response
    {
        $perimeter = new Perimeter();
        $form = $this->createForm(PerimeterType::class, $perimeter);
        $form->submit(json_decode($request->getContent(), true));

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $existing = $entityManager->getRepository(Perimeter::class)->findBy(
                ['latitude' => $form->get('latitude')->getData()]
            );

            if (count($existing) == 0) {
                $entityManager->persist($perimeter);
                $entityManager->flush();
                $data = $serializer->serialize(
                    $perimeter,
                    'json',
                    SerializationContext::create()->enableMaxDepthChecks()
                );

                return new JsonResponse($data, Response::HTTP_CREATED, [], true);
            }
            $error = [
                'error' => 'Point already exists',
            ];
            $data = $serializer->serialize($error, 'json', SerializationContext::create()->enableMaxDepthChecks());

            return new JsonResponse($data, Response::HTTP_CONFLICT, [], true);
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
     *     tags={"Perimeter"},
     *     @SWG\Parameter(
     *         name="Perimeter ID",
     *         type="integer",
     *         in="body",
     *         @SWG\Schema(ref=@Model(type=PerimeterType::class))
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Edited perimeter",
     *         @SWG\Schema(ref=@Model(type=Perimeter::class))
     *     )
     * )
     * @Route("/{id}", name="perimeter_edit", methods={"PUT"})
     * @param Request $request
     * @param Perimeter $perimeter
     * @param $serializer
     * @return Response
     */
    public function edit(Request $request, Perimeter $perimeter, SerializerInterface $serializer): Response
    {
        $form = $this->createForm(PerimeterType::class, $perimeter);
        $form->submit(json_decode($request->getContent(), true));

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $data = $serializer->serialize($perimeter, 'json', SerializationContext::create()->enableMaxDepthChecks());

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
     *     tags={"Perimeter"},
     *     @SWG\Parameter(
     *         name="Perimeter ID",
     *         type="integer",
     *         in="body",
     *         @SWG\Schema()
     *     ),
     *     @SWG\Response(
     *         response=202,
     *         description="Perimeter",
     *         @SWG\Schema()
     *     )
     * )
     * @Route("/{id}", name="perimeter_delete", methods={"DELETE"})
     * @param Perimeter $perimeter
     * @param SerializerInterface $serializer
     * @return Response
     */
    public function delete(Perimeter $perimeter, SerializerInterface $serializer): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($perimeter);
        $entityManager->flush();

        $data = $serializer->serialize($perimeter, 'json', SerializationContext::create()->enableMaxDepthChecks());

        return new JsonResponse($data, Response::HTTP_ACCEPTED, [], true);
    }

    /**
     * @SWG\Get
     * (
     *     tags={"Perimeter"},
     * @SWG\Parameter(
     *         name="Site ID",
     *         in="body",
     *         type="interger",
     *         @SWG\Schema()
     *     ),
     * @SWG\Response(
     *         response=200,
     *         description="Centroid of the polygon",
     *         @SWG\Schema()
     *     )
     * )
     * @Route("/geofence/{id}", name="perimeter_geofence", methods={"GET"})
     * @param GeoFenceHelper $geoFenceHelper
     * @param SerializerInterface $serializer
     * @param $id
     * @return Response
     */
    public function geofence(GeoFenceHelper $geoFenceHelper, SerializerInterface $serializer, $id): Response
    {
        $answer = [
            'center' => $geoFenceHelper->getCentroid($id),
            'points' => $this->getDoctrine()->getRepository(Perimeter::class)->findBy(['site' => $id]),
        ];

        $data = $serializer->serialize($answer, 'json', SerializationContext::create()->enableMaxDepthChecks());

        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }
}
