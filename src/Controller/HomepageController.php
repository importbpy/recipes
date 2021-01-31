<?php

declare(strict_types = 1);

namespace App\Controller;

use App\Model\Recipe\DefaultRecipeRepository;
use App\Model\Tag\DefaultTaggingRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

class HomepageController extends AbstractController
{

	private DefaultRecipeRepository $recipeRepository;

    private DefaultTaggingRepository $taggingRepository;

    public function __construct(
        DefaultRecipeRepository $recipeRepository,
        DefaultTaggingRepository $taggingRepository
	) {
		$this->recipeRepository = $recipeRepository;
        $this->taggingRepository = $taggingRepository;
    }

	/**
	 * @Route(
	 *     "/",
     *     name="homepage"
	 * )
	 */
	public function homepage(Request $request)
	{
	    $tags = $request->query->get('tags');
	    if ($tags === null || strlen($tags) === 0) {
            $recipes = $this->recipeRepository->getRecipes();
        } else {
	        $tagArray = explode(',', $tags);
	        try {
                $recipes = array_map(fn($tagging) => $tagging->getRecipe(), $this->taggingRepository->findByTagNames($tagArray));
            } catch (Throwable $exception) {
	            var_dump($exception->getMessage());
	            $recipes = [];
            }
        }

		return $this->render('homepage.html.twig', [
			'recipes' => $recipes,
		]);
	}

}