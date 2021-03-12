<?php

declare(strict_types = 1);

namespace App\Model\Tag;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Tag|null find($id, $lockMode = null, $lockVersion = null)
 * @method Tag[]    findAll()
 */
final class DefaultTagRepository extends ServiceEntityRepository implements TagRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tag::class);
    }

    public function getById(string $id): ?Tag {
        return $this->find($id);
    }

    public function getIdsByTagNames(array $tagNames): array
    {
        $result = $this->createQueryBuilder('tag')
            ->select('recipe.id')
            ->addSelect('count(recipe.id) AS tagCount')
            ->innerJoin('tag.recipes', 'recipe')
            ->where('tag.name IN (:tagNames)')
            ->groupBy('recipe.id')
            ->having('tagCount = :tagCount')
            ->getQuery()
            ->execute([
                'tagNames' => $tagNames,
                'tagCount' => count($tagNames)
            ]);
        return array_map(fn ($row) => $row['id'], $result);
    }
}