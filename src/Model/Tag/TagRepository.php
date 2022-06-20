<?php

declare(strict_types = 1);

namespace App\Model\Tag;

/**
 * @method Tag|null find($id, $lockMode = null, $lockVersion = null)
 * @method Tag[]    findAll()
 */
interface TagRepository
{

    public function getById(string $id): ?Tag;

    /**
     * @param array $tagNames
     * @return array<\App\Model\Tag\Tag>
     */
    public function getIdsByTagNames(array $tagNames): array;

}