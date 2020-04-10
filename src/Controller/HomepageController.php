<?php

declare(strict_types = 1);

namespace App\Controller;

use App\Repository\RecipeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class HomepageController extends AbstractController
{

	/**
	 * @var \App\Entity\RecipeRepository
	 */
	private $recipeRepository;

	public function __construct(
		RecipeRepository $recipeRepository
	) {
		$this->recipeRepository = $recipeRepository;
	}

	/**
	 * @Route(
	 *     "/",
     *     name="homepage"
	 * )
	 */
	public function homepage()
	{
		$recipes = $this->recipeRepository->getRecipes();

		$newRecipeUrl = $this->generateUrl('new_recipe');
		return $this->render('homepage.html.twig', [
			'recipes' => $recipes,
			'newRecipe' => $newRecipeUrl,
		]);
	}

}