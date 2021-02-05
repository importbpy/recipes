<?php

declare(strict_types = 1);

namespace App\Controller;

use App\Model\Recipe\NewRecipe;
use App\Model\Recipe\Recipe;
use App\Model\Recipe\RecipeRepository;
use App\Model\Tag\Tagging;
use App\Model\Tag\TaggingRepository;
use App\Model\Tag\TagRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class AdminController extends AbstractController
{

    private EntityManagerInterface $entityManager;

    private TagRepository $tagRepository;

    private TaggingRepository $taggingRepository;

    private RecipeRepository $recipeRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        TagRepository $tagRepository,
        TaggingRepository $taggingRepository,
        RecipeRepository $recipeRepository
    )
    {
        $this->entityManager = $entityManager;
        $this->tagRepository = $tagRepository;
        $this->recipeRepository = $recipeRepository;
        $this->taggingRepository = $taggingRepository;
    }

    /**
     * @Route(
     *     "/admin",
     *     name="admin_homepage"
     * )
     */
    public function adminHomepage(): Response
    {
        return $this->render(
            'admin.base.html.twig'
        );
    }

    /**
     * @Route(
     *     "/admin/new-recipe",
     *     name="create_new_recipe"
     * )
     */
    public function addNewRecipe(Request $request): Response
    {
        $newRecipe = new NewRecipe();

        $form = $this->createFormBuilder($newRecipe)
            ->add('title', TextType::class)
            ->add('description', TextareaType::class)
            ->add('image', FileType::class)
            ->add('submit', SubmitType::class)
            ->getForm()
        ;

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $recipe = new Recipe(
                $newRecipe->getTitle(),
                $newRecipe->getDescription(),
                null,
            );
            $this->entityManager->persist($recipe);

            $this->entityManager->flush();
            $image = $newRecipe->getImage();
            if ($image !== null) {
                $image->move(__DIR__ . '/../../public/images/original', $recipe->getSlug() . '.jpg');
            }

            $image = imagecreatefromjpeg(__DIR__ . '/../../public/images/original/' . $recipe->getSlug() . '.jpg');
            $imgResized = imagescale($image , 400);
            imagejpeg($imgResized, __DIR__ . '/../../public/images/small/' . $recipe->getSlug() . '.jpg');
            $imgResized = imagescale($image , 1200);
            imagejpeg($imgResized, __DIR__ . '/../../public/images/' . $recipe->getSlug() . '.jpg');

            return $this->redirectToRoute('admin_homepage');
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

        $tagging = new Tagging($recipe, $tag);
        $this->entityManager->persist($tagging);
        $this->entityManager->flush();

        return $this->redirectToRoute('edit_recipe', ['slug' => $recipe->getSlug()]);
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
        $this->taggingRepository->delete($recipeId, $tagId);
        $recipe = $this->recipeRepository->getById($recipeId);
        if ($recipe === null) {
            return new Response('Recipe not found', Response::HTTP_NOT_FOUND);
        }
        return $this->redirectToRoute('edit_recipe', ['slug' => $recipe->getSlug()]);
    }

    /**
     * @Route(
     *     "/admin/edit/recipe/{slug}",
     *     name="edit_recipe",
     *     requirements={"slug"="[a-z\-]+"}
     * )
     */
    public function editRecipe(string $slug): Response
    {
        $recipe = $this->recipeRepository->findOneBySlug($slug);
        if ($recipe === null) {
            return new Response('Recipe not found', Response::HTTP_NOT_FOUND);
        }

        $taggings = $this->taggingRepository->findByRecipe($recipe);

        $tags = $this->tagRepository->findAll();


        return $this->render('/admin/editRecipe.html.twig', [
            'recipe' => $recipe,
            'currentTags' => array_map(fn ($tagging) => $tagging->getTag(), $taggings),
            'availableTags' => $tags,
        ]);
    }

}