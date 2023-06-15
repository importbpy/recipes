<?php

declare(strict_types=1);

namespace App\Controller;

use App\Model\User\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route as Route;
use Symfony\Component\Security\Core\Security;

final class UserController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    private UserRepository $userRepository;
    private Security $security;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        Security $security
    ) {
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
        $this->security = $security;
    }

    /**
     * @Route(
     *     "/users",
     *     name="edit_users"
     * )
     */
    public function editUsers(): Response
    {
        $users = $this->userRepository->findAll();

        return $this->render('/admin/editUsers.html.twig', [
            'users' => $users,
        ]);
    }

    /**
     * @Route(
     *     "/users/delete-user/{userId}",
     *     name="delete_user",
     *     requirements={"userId"="\d+"},
     *     methods={"GET"}
     * )
     */
    public function deleteUser(string $userId): Response
    {
        $user = $this->userRepository->find($userId);
        if ($user === null) {
            throw new NotFoundHttpException();
        }

        $currentUser = $this->security->getUser();
        if ($currentUser->getUserIdentifier() === $user->getUserIdentifier()) {
            throw new AccessDeniedHttpException();
        }

        $this->entityManager->remove($user);
        $this->entityManager->flush();

        return $this->redirectToRoute('edit_users');
    }

    /**
     * @Route(
     *     "/users/edit-role/{userId}/{role}",
     *     name="change_user_role",
     *     requirements={"userId"="\d+", "role"="[A-Z_]+"},
     *     methods={"GET"}
     * )
     */
    public function changeUserRole(string $userId, string $role): Response
    {
        $user = $this->userRepository->find($userId);
        if ($user === null) {
            throw new NotFoundHttpException();
        }

        $currentUser = $this->security->getUser();
        if ($currentUser->getUserIdentifier() === $user->getUserIdentifier()) {
            throw new AccessDeniedHttpException();
        }

        if (!in_array($role, ['ROLE_USER', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN'], true)) {
            throw new BadRequestException();
        }

        $roles = [];

        if ($role === 'ROLE_ADMIN' || $role === 'ROLE_SUPER_ADMIN') {
            $roles[] = 'ROLE_ADMIN';
        }

        if ($role === 'ROLE_SUPER_ADMIN') {
            $roles[] = 'ROLE_SUPER_ADMIN';
        }

        $user->setRoles($roles);
        $this->entityManager->flush();

        return $this->redirectToRoute('edit_users');
    }
}
