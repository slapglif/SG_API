<?php

namespace App\Controller\Site;

use App\Entity\Site;
use App\Form\SitesType;
use App\Repository\SiteRepository;
use App\Services\ActivityLogHelper;
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
 * @Route("/api/sites")
 * @IsGranted({"ROLE_ADMIN"})
 */
class SitesController extends AbstractFOSRestController
{
    /**
     * @SWG\Get(
     *     tags={"Sites"},
     *     @SWG\Response(
     *         response=200,
     *         description="Return Site model.",
     *         @SWG\Schema(ref=@Model(type=Site::class))
     *     )
     * )
     * @IsGranted("ROLE_GUARD")
     * @Route("", name="sites_index", methods={"GET"})
     * @param SiteRepository $sitesRepository
     * @return Response
     */
    public function index(SiteRepository $sitesRepository): Response
    {
        $sites = $sitesRepository->findAll();

        return $this->handleView($this->view($sites));
    }

    /**
     * @SWG\Post(
     *     tags={"Sites"},
     *     @SWG\Parameter(
     *         name="Site creation",
     *     type="form",
     *     in="body",
     *         required=true,
     *         @SWG\Schema(ref=@Model(type=SitesType::class))
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Site model",
     *         @SWG\Schema(ref=@Model(type=Site::class))
     *     )
     * )
     * @IsGranted("ROLE_ADMIN")
     * @Route("/new", name="sites_new", methods={"POST"})
     * @param Request $request
     * @return Response
     */
    public function new(Request $request): Response
    {
        $site = new Site();
        $form = $this->createForm(SitesType::class, $site);
        $form->submit(json_decode($request->getContent(), true));

        $existing = $this->getDoctrine()->getRepository(Site::class)->findOneBy(
            ['name' => $form->getData()->getName()]
        );

        if ($existing !== null) {
            $view = $this->view(['Error' => 'Site '.$existing->getName().' already exists'], Response::HTTP_CONFLICT);

            return $this->handleView($view);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($form->getData());
            $entityManager->flush();

            return $this->handleView($this->view($form->getData()));
        }

        return $this->handleView($this->view($form->getErrors()));
    }

    /**
     * @SWG\Get(
     *     tags={"Sites"},
     *     @SWG\Response(
     *     response=200,
     *     description="Site Model",
     *     @SWG\Schema(ref=@Model(type="Site::class"))
     *     )
     * )
     * @IsGranted("ROLE_GUARD")
     * @Route("/{id}", name="sites_show", methods={"GET"})
     * @param Site $site
     * @return Response
     */
    public function show(Site $site): Response
    {
        return $this->handleView($this->view($site));
    }

    /**
     * @SWG\Put(
     *     tags={"Sites"},
     *     @SWG\Parameter(
     *         name="Query details.",
     *         in="body",
     *         required=true,
     *         type="array",
     *         description="Querys for editing site details.",
     *         @SWG\Schema(
     *             @SWG\Property(property="name",type="string",description="Sites name."),
     *             @SWG\Property(property="active", type="boolean",description="Sites active or not."),
     *             @SWG\Property(property="description", type="string",description="Description of the site."),
     *         )
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Site Model",
     *         @SWG\Schema(ref=@Model(type=Site::class))
     *     )
     * )
     * @IsGranted("ROLE_ADMIN")
     * @Route("/{id}", name="sites_edit", methods={"PUT"})
     * @param Request $request
     * @param Site $site
     * @return Response
     */
    public function edit(Request $request, Site $site): Response
    {
        $site->setName($request->request->get("name"));
        $site->setActive($request->request->get("active"));
        $site->setDescription($request->request->get("description"));

        $this->getDoctrine()->getManager()->flush();

        return $this->handleView($this->view($site));
    }

    /**
     * @SWG\Delete(
     *     tags={"Sites"},
     *     @SWG\Response(
     *     response=200,
     *     description="Site model",
     *     @SWG\Schema(ref=@Model(type=Site::class))
     *     )
     * )
     * @IsGranted("ROLE_ADMIN")
     * @Route("/{site}", name="sites_delete", methods={"DELETE"})
     * @param Site $site
     * @param SerializerInterface $serializer
     * @return Response
     */
    public function delete(Site $site, SerializerInterface $serializer): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($site);
        $entityManager->flush();

        $data = $serializer->serialize($site, 'json', SerializationContext::create()->enableMaxDepthChecks());

        return new JsonResponse($data, Response::HTTP_ACCEPTED, [], true);
    }
}
