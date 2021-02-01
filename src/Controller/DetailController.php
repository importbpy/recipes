<?php

declare(strict_types = 1);

namespace App\Controller;

use App\Model\Recipe\RecipeRepository;
use Parsedown;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class DetailController extends AbstractController
{

    private RecipeRepository $recipeRepository;

    public function __construct(
        RecipeRepository $recipeRepository
    ) {
        $this->recipeRepository = $recipeRepository;
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

        return $this->render('detail.html.twig', ['recipe' => $recipe]);
    }

}