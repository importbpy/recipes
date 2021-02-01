<?php

declare(strict_types = 1);

namespace App\Controller;

use App\Model\Recipe\DefaultRecipeRepository;
use App\Model\Tag\DefaultTaggingRepository;
use App\Model\Tag\TagRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

class HomepageController extends AbstractController
{

	private DefaultRecipeRepository $recipeRepository;

    private DefaultTaggingRepository $taggingRepository;

    private TagRepository $tagRepository;

    public function __construct(
        DefaultRecipeRepository $recipeRepository,
        DefaultTaggingRepository $taggingRepository,
        TagRepository $tagRepository
	) {
		$this->recipeRepository = $recipeRepository;
        $this->taggingRepository = $taggingRepository;
        $this->tagRepository = $tagRepository;
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

	    $tags = $this->tagRepository->findAll();

		return $this->render('homepage.html.twig', [
			'recipes' => $recipes,
			'tags' => $tags,
		]);
	}

}