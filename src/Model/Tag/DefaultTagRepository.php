<?php

declare(strict_types = 1);

namespace App\Model\Tag;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class DefaultTagRepository extends ServiceEntityRepository implements TagRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tag::class);
    }

    public function getById(string $id): ?Tag {
        return $this->find($id);
    }

}