<?php

namespace App\Controller\User;

use App\Entity\User;
use App\Form\UserType;
use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @IsGranted("ROLE_GUARD")
 * @Route("/api/user/profile", name="app_user_")
 */
class AccountController extends AbstractFOSRestController
{
    /**
     * @SWG\Get(
     *     tags={"User"},
     *     @SWG\Parameter(
     *         name="username",
     *         in="body",
     *         type="string",
     *         description="Users username",
     *         @SWG\Schema()
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Returns current users information",
     *     @SWG\Schema(ref=@Model(type=User::class))
     *     )
     * )
     * @Route("", name="profile_own", methods={"GET"})
     */
    public function profile()
    {
        $token = $this->getUser()->getUsername();
        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(['email' => $token]);

        $view = $this->view($user);
        $view->getContext()->addGroups($user->getRoles());

        return $this->handleView($view);
    }

    /**
     * @SWG\Get(
     *     tags={"User"},
     *     @SWG\Parameter(
     *         name="uuid",
     *         in="query",
     *         type="integer",
     *         description="Users uuid number",
     *         @SWG\Schema()
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Returns selected users information",
     *     @SWG\Schema(ref=@Model(type=User::class))
     *     )
     * )
     * @Route("/{uuid}", name="profile_get", methods={"GET"})
     * @param $uuid
     * @return Response
     */
    public function getAction($uuid)
    {
        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(['id' => $uuid]);

        $view = $this->view($user, 200);
        $context = new Context();
        $view->setContext($context);

        return $this->handleView($view);
    }

    /**
     * @SWG\Put(
     *     tags={"User"},
     *     @SWG\Parameter(
     *         name="username",
     *         in="body",
     *         type="string",
     *         description="Users ",
     *         @SWG\Schema(ref=@Model(type=UserType::class))
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Edit current users information",
     *     @SWG\Schema(ref=@Model(type=User::class))
     *     )
     * )
     * @SWG\Patch(
     *     tags={"User"},
     *     @SWG\Parameter(
     *         name="username",
     *         in="body",
     *         type="string",
     *         description="Users ",
     *         @SWG\Schema(ref=@Model(type=UserType::class))
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Edit current users information",
     *     @SWG\Schema(ref=@Model(type=User::class))
     *     )
     * )
     * @Route("/{uuid}", name="profile_edit", methods={"PUT","PATCH"})
     * @param Request $request
     * @param $uuid
     */
    public function editAction(Request $request, $uuid)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $userRepository = $this->getDoctrine()->getRepository(User::class);

        $user = $userRepository->findOneBy(['id' => $uuid]);

        $form = $this->createForm(UserType::class, $user);
        $clearMissing = $request->getMethod() !== 'PATCH';
        $form->submit($request->request->all(), $clearMissing);

        $entityManager->flush();

        $view = $this->view($entityManager);
        $view->getContext()->addGroups($user->getRoles());

        return $this->handleView($view);
    }
}
