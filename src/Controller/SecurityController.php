<?php

namespace App\Controller;

use App\Form\ChangePasswordFormType;
use App\Model\User\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route as Route;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Contracts\Translation\TranslatorInterface;

class SecurityController extends AbstractController
{
    private UserRepository $userRepository;
    private EntityManagerInterface $entityManager;
    private TranslatorInterface $translator;

    public function __construct(
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        TranslatorInterface $translator
    ) {
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
        $this->translator = $translator;
    }

    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils, Request $request): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        $registered = $request->get('registered') === '1';

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'justRegistered' => $registered,
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    /**
     * @Route("/change-password", name="change_password")
     */
    public function editPassword(Request $request, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = $this->getUser();

        $userId = $user->getUserIdentifier();
        $userEntity = $this->userRepository->find($userId);

        if (!$user instanceof PasswordAuthenticatedUserInterface) {
            return $this->redirectToRoute('app_login');
        }

        $form = $this->createForm(ChangePasswordFormType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $currentPassword = $form->get('currentPassword')->getData();
            if (!$passwordHasher->isPasswordValid($user, $currentPassword)) {
                $form->addError(new FormError($this->translator->trans('Current password is not correct.')));
                return $this->render('security/changePassword.html.twig', [
                    'form' => $form->createView(),
                ]);
            }
            $password = $form->get('password')->getData();
            $confirmPassword = $form->get('confirmPassword')->getData();

            if ($password !== $confirmPassword) {
                $form->addError(new FormError($this->translator->trans('Passwords are not the same.')));
                return $this->render('security/changePassword.html.twig', [
                    'form' => $form->createView(),
                ]);
            }

            $hashedPassword = $passwordHasher->hashPassword($user, $password);

            $userEntity->setPassword($hashedPassword);
            $this->entityManager->flush();

            $this->addFlash('success', $this->translator->trans('Password updated successfully.'));
            return $this->redirectToRoute('homepage'); // Redirect to a success page
        }

        return $this->render('security/changePassword.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
