<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * @param AuthenticationUtils $authenticationUtils
     * @Route("/login", name="app_login")
     * @return Response
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        /**
         * get the login error if there is one
         */
        $error = $authenticationUtils->getLastAuthenticationError();
        /**
         * last username entered by the user
         */
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * This action has been used for signUp User
     *
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @return Response
     * @Route("/sign-up", name="sign_up")
     * @author Mehran
     */
    public function signUp(Request $request, UserPasswordEncoderInterface $passwordEncoder): Response
    {
        $user = new User();
        $entityManager = $this->getDoctrine()->getManager();
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->getConnection()->beginTransaction();
            try {
                $user->setPassword($passwordEncoder->encodePassword($user, $user->getPassword()));

                $entityManager->persist($user);
                $entityManager->flush();
                $entityManager->getConnection()->commit();

                $this->addFlash("success", "New User has been created successfully :);)");
                return $this->redirectToRoute('app_login');
            } catch (Exception $e) {
                $entityManager->getConnection()->rollBack();
                $this->addFlash("error", "There is some problem you can not create user :(:(");
                return $this->redirectToRoute('sign_up');
            }
        }

        return $this->render('security/SignUp.html.twig', [
            'form' => $form->createView(),
            'errors' => $form->getErrors()
        ]);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
        return $this->redirectToRoute('app_login');
    }
}
