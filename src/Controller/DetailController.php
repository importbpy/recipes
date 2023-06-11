<?php

declare(strict_types=1);

namespace App\Controller;

use App\Model\Recipe\RecipeRepository;
use App\Model\Tag\Tag;
use App\Model\Tag\TagRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route as Route;

final class DetailController extends AbstractController
{
    private RecipeRepository $recipeRepository;
    private TagRepository $tagRepository;

    public function __construct(
        RecipeRepository $recipeRepository,
        TagRepository $tagRepository
    ) {
        $this->recipeRepository = $recipeRepository;
        $this->tagRepository = $tagRepository;
    }

    /**
     * @Route(
     *     "/recept/{slug}",
     *     name="detail"
     * )
     */
    public function detail(string $slug): Response
    {
        $recipe = $this->recipeRepository->findOneBySlug($slug);
        if ($recipe === null) {
            return new Response('Recipe not found', 404);
        }

        $currentTags = $recipe->getTags()->toArray();
        $currentTagIds = array_map(fn (Tag $tag) => $tag->getId(), $currentTags);
        $tags = array_filter($this->tagRepository->findAll(), function (Tag $tag) use ($currentTagIds): bool {
            return !in_array($tag->getId(), $currentTagIds);
        });

        return $this->render('detail.html.twig', [
            'recipe' => $recipe,
            'currentTags' => $currentTags,
            'availableTags' => $tags,
        ]);
    }
}
