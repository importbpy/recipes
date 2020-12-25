<?php

declare(strict_types = 1);

namespace App\Controller;

use App\Model\Recipe\DefaultRecipeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class HomepageController extends AbstractController
{

	private DefaultRecipeRepository $recipeRepository;

	public function __construct(
        DefaultRecipeRepository $recipeRepository
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