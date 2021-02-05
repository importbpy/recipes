<?php

declare(strict_types = 1);

namespace App\Model\Tag;

use App\Model\Recipe\Recipe;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

final class DefaultTaggingRepository extends ServiceEntityRepository implements TaggingRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tagging::class);
    }

    public function findByTagNames(array $tagNames): array
    {
        return $this->createQueryBuilder('tagging')
            ->select('tagging')
            ->join('tagging.recipe', 'recipe')
            ->join('tagging.tag', 'tag')
            ->where('tag.name IN (:tagNames)')
            ->groupBy('recipe')
            ->setParameter('tagNames', $tagNames)
            ->orderBy('recipe.slug')
            ->getQuery()
            ->execute();
    }

    public function findByRecipe(Recipe $recipe): array
    {
        return $this->findBy(['recipe' => $recipe]);
    }

    public function delete(string $recipeId, string $tagId): void
    {
        $tagging = $this->createQueryBuilder('tagging')
            ->select('tagging')
            ->join('tagging.recipe', 'recipe')
            ->join('tagging.tag', 'tag')
            ->where('recipe.id = :recipeId')
            ->andWhere('tag.id = :tagId')
            ->getQuery()
            ->execute(['recipeId' => $recipeId, 'tagId' => $tagId, ]);
        if ($tagging !== null && isset($tagging[0])) {
            $entityManager = $this->getEntityManager();
            $entityManager->remove($tagging[0]);
            $entityManager->flush();
        }
    }

}