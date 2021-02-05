<?php

declare(strict_types = 1);

namespace App\Model\Tag;

interface TagRepository
{

    public function getById(string $id): ?Tag;

}