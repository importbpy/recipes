<?php

declare(strict_types = 1);

namespace App\Model\Recipe;

interface RecipeRepository
{

    public function findOneBySlug(string $slug): ?Recipe;

    public function getById(string $id): ?Recipe;

}