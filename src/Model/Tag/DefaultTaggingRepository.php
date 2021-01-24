<?php

declare(strict_types = 1);

namespace App\Model\Tag;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

final class DefaultTaggingRepository extends ServiceEntityRepository
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
            ->groupBy('tagging.recipe')
            ->setParameter('tagNames', $tagNames)
            ->getQuery()
            ->execute();
    }

}