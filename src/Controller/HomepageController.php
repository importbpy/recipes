<?php

declare(strict_types=1);

namespace App\Controller;

use App\Model\Recipe\DefaultRecipeRepository;
use App\Model\Tag\TagRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class HomepageController extends AbstractController
{
    private const ORDER = [
        'az-asc' => ['slug' => 'ASC'],
        'az-desc' => ['slug' => 'DESC'],
        'date-asc' => ['id' => 'ASC'],
        'date-desc' => ['id' => 'DESC'],
    ];

    private DefaultRecipeRepository $recipeRepository;

    private TagRepository $tagRepository;

    public function __construct(
        DefaultRecipeRepository $recipeRepository,
        TagRepository $tagRepository
    ) {
        $this->recipeRepository = $recipeRepository;
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
        $sort = $request->query->get('sort') ?? 'az-asc';
        $order = self::ORDER[$sort] ?? [];
        if ($tags === null || strlen($tags) === 0) {
            $recipes = $this->recipeRepository->findBy([], $order);
        } else {
            $tagArray = explode(',', $tags);
            $recipeIds = $this->tagRepository->getIdsByTagNames($tagArray);
            $recipes = $this->recipeRepository->findBy(['id' => $recipeIds], $order);
        }

        $tags = $this->tagRepository->findAll();

        return $this->render('homepage.html.twig', [
            'recipes' => $recipes,
            'tags' => $tags,
            'sort' => [
                'az' => [
                    'query' => $sort !== 'az-asc' ? 'az-asc' : 'az-desc',
                    'label' => $sort !== 'az-asc' ? 'A/Z ↓' : 'A/Z ↑',
                ],
                'date' => [
                    'query' => $sort !== 'date-desc' ? 'date-desc' : 'date-asc',
                    'label' => $sort !== 'date-desc' ? '📅 ↑' : '📅 ↓',
                ],
            ]
        ]);
    }
}
