<?php

declare(strict_types=1);

namespace App\Controller;

use App\Form\RecipeFormType;
use App\Form\TagFormType;
use App\Model\Image\ImageManager;
use App\Model\Recipe\Recipe;
use App\Model\Recipe\RecipeRepository;
use App\Model\Recipe\RecipeTemplate;
use App\Model\Tag\Tag;
use App\Model\Tag\TagRepository;
use App\Model\User\UserRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route as Route;
use Symfony\Component\Security\Core\Security;

final class AdminController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    private TagRepository $tagRepository;

    private RecipeRepository $recipeRepository;
    private ImageManager $imageManager;
    private UserRepository $userRepository;
    private Security $security;

    public function __construct(
        EntityManagerInterface $entityManager,
        TagRepository $tagRepository,
        RecipeRepository $recipeRepository,
        ImageManager $imageManager,
        UserRepository $userRepository,
        Security $security
    ) {
        $this->entityManager = $entityManager;
        $this->tagRepository = $tagRepository;
        $this->recipeRepository = $recipeRepository;
        $this->imageManager = $imageManager;
        $this->userRepository = $userRepository;
        $this->security = $security;
    }

    /**
     * @Route(
     *     "/admin/new-recipe",
     *     name="create_new_recipe"
     * )
     */
    public function addNewRecipe(Request $request): Response
    {
        $newRecipe = new Recipe('', RecipeTemplate::getDescriptionTemplate(), null); // This Recipe instance is here to retrieve data from the form

        $form = $this->createForm(RecipeFormType::class, $newRecipe);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $recipe = new Recipe(
                $newRecipe->getTitle(),
                $newRecipe->getDescription(),
                $newRecipe->getLink(),
            );

            $image = $form->get('image')->getData();
            if ($image !== null) {
                $this->imageManager->saveImage($image, $recipe->getSlug());
            }

            $this->entityManager->persist($recipe);
            try {
                $this->entityManager->flush();
            } catch (UniqueConstraintViolationException) {
                $form->addError(new FormError('The name is already taken. Try another one.'));
                return $this->render(
                    '/admin/addNewRecipe.html.twig',
                    [
                        'form' => $form->createView(),
                    ]
                );
            }

            return $this->redirectToRoute('homepage');
        }

        return $this->render(
            '/admin/addNewRecipe.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route(
     *     "/admin/edit/recipe/{recipeId}",
     *     name="edit_recipe",
     *     requirements={"recipeId"="\d+"}
     * )
     */
    public function editRecipe(Request $request, string $recipeId): Response
    {
        $recipe = $this->recipeRepository->getById($recipeId);

        $form = $this->createForm(RecipeFormType::class, $recipe);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $image = $form->get('image')->getData();
            if ($image) {
                $this->imageManager->saveImage($image, $recipe->getSlug());
                $recipe->bustCache();
            }

            $this->entityManager->flush();
            return $this->redirectToRoute('detail', ['slug' => $recipe->getSlug()]);
        }

        return $this->render(
            '/admin/editRecipe.html.twig',
            [
                'form' => $form->createView(),
                'recipe' => $recipe,
            ]
        );
    }

    /**
     * @Route(
     *     "/admin/delete-recipe/{recipeId}",
     *     name="delete_recipe",
     *     requirements={"recipeId"="\d+"},
     *     methods={"GET"}
     * )
     */
    public function deleteRecipe(string $recipeId): Response
    {
        $this->recipeRepository->deleteRecipe($recipeId);

        return $this->redirectToRoute('homepage');
    }

    /**
     * @Route(
     *     "/admin/tag-recipe/{recipeId}/{tagId}",
     *     name="tag_recipe",
     *     requirements={"recipeId"="\d+", "tagId"="\d+"},
     *     methods={"GET"}
     * )
     */
    public function tagRecipe(string $recipeId, string $tagId): Response
    {
        $recipe = $this->recipeRepository->getById($recipeId);
        if ($recipe === null) {
            return new Response('Could not find the recipe', Response::HTTP_NOT_FOUND);
        }
        $tag = $this->tagRepository->getById($tagId);
        if ($tag === null) {
            return new Response('Could not find the tag', Response::HTTP_NOT_FOUND);
        }

        $recipe->addTag($tag);

        $this->entityManager->flush();

        return $this->redirectToRoute('detail', ['slug' => $recipe->getSlug()]);
    }

    /**
     * @Route(
     *     "/admin/remove-tag/{recipeId}/{tagId}",
     *     name="remove_tag",
     *     requirements={"recipeId"="\d+", "tagId"="\d+"},
     *     methods={"GET"}
     * )
     */
    public function removeTag(string $recipeId, string $tagId): Response
    {
        $recipe = $this->recipeRepository->getById($recipeId);
        if ($recipe === null) {
            return new Response('Recipe not found', Response::HTTP_NOT_FOUND);
        }

        $recipe->getTags()->removeElement($this->tagRepository->getById($tagId));
        $this->entityManager->flush();

        return $this->redirectToRoute('detail', ['slug' => $recipe->getSlug()]);
    }

    /**
     * @Route(
     *     "/admin/edit/tags",
     *     name="edit_tags"
     * )
     */
    public function editTags(): Response
    {
        $tags = $this->tagRepository->findAll();

        return $this->render(
            '/admin/editTags.html.twig',
            ['tags' => $tags]
        );
    }

    /**
     * @Route(
     *     "/admin/tag/create",
     *     name="create_tag"
     * )
     */
    public function createTag(Request $request): Response
    {
        $newTag = new Tag('', '');
        $form = $this->createForm(TagFormType::class, $newTag);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($newTag);
            try {
                $this->entityManager->flush();
            } catch (UniqueConstraintViolationException) {
                $form->addError(new FormError('The name is already taken. Try another one.'));
                return $this->render(
                    '/admin/addNewTag.html.twig',
                    [
                        'form' => $form->createView(),
                    ]
                );
            }

            return $this->redirectToRoute('edit_tags');
        }

        return $this->render(
            '/admin/addNewTag.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route(
     *     "/admin/delete-tag/{tagId}",
     *     name="delete_tag",
     *     requirements={"tagId"="\d+"},
     *     methods={"GET"}
     * )
     */
    public function deleteTag(string $tagId): Response
    {
        $this->tagRepository->deleteTag($tagId);

        return $this->redirectToRoute('edit_tags');
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
