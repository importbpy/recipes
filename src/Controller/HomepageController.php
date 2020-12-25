<?php

declare(strict_types = 1);

namespace App\Controller;

use App\Model\Recipe\RecipeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class HomepageController extends AbstractController
{

	private RecipeRepository $recipeRepository;

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

		return $this->render('homepage.html.twig', [
			'recipes' => $recipes,
		]);
	}

}