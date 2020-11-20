<?php

namespace App\Controller\User;

use App\Entity\User;
use DateTime;
use Exception;
use LogicException;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * @Route("/api/auth", name="app_auth_")
 */
class SecurityController extends AbstractController
{
    /**
     * @SWG\Post(
     *     tags={"Auth"},
     *     @SWG\Parameter(
     *         name="username",
     *         in="body",
     *         required=true,
     *         description="Users Username",
     *         type="string",
     *         @SWG\Schema(
     *             type="string",
     *         )
     *     ),
     *     @SWG\Parameter(
     *         name="password",
     *         in="body",
     *         required=true,
     *         description="Password",
     *         type="string",
     *         @SWG\Schema(
     *             type="string"
     *         )
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Login method for users.",
     *             @SWG\Schema(),
     *     )
     * )
     * @Route("/login", name="login", methods={"POST"})
     * @param AuthenticationUtils $authenticationUtils
     * @return Response
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @SWG\Post(
     *     tags={"Auth"},
     *     @SWG\Response(
     *         response=200,
     *         description="Logout method."
     *     )
     * )
     * @Route("/logout", name="logout", methods={"POST"})
     */
    public function logout()
    {
        throw new LogicException('');
    }

    /**
     * @SWG\Post(
     *     tags={"Auth"},
     *     @SWG\Parameter(
     *         name="",
     *         in="body",
     *         required=true,
     *         description="Users intended information",
     *         type="array",
     *         @SWG\Schema(
     *             type="string",
     *             @SWG\Property(property="email", ref=@Model(type="User::class"))
     *         )
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="User %s successfully created"
     *     )
     * )
     * @Route("/register", name="register", methods={"POST"})
     * @param Request $request
     * @param UserPasswordEncoderInterface $encoder
     * @return Response
     * @throws Exception
     */
    public function register(Request $request, UserPasswordEncoderInterface $encoder)
    {

        if ($request->isMethod("POST")) {
            $username = $request->request->get('email');
            $password = $request->request->get('password');

            $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(['email' => $username]);

            if (!is_null($user)) {
                return $this->view(
                    [
                        'message' => 'User already exists',
                    ],
                    Response::HTTP_CONFLICT
                );
            }

            $user = new User();

            $user->setEmail($username);
            $user->setFirstName($request->request->get('firstName'));
            $user->setLastName($request->request->get('lastName'));
            $user->setPassword($encoder->encodePassword($user, $password));
            $user->setRegistrationDate($this->date = new DateTime());
            $user->setLastLoggedIn($this->date = new DateTime());
            $user->setActive(true);

            $this->getDoctrine()->getManager()->persist($user);
            $this->getDoctrine()->getManager()->flush();

            return new Response(sprintf('User %s successfully created', $user->getUsername()));
        }

        return $this->render('security/register.html.twig');
    }
}
