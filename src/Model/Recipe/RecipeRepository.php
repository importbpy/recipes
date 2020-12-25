<?php

declare(strict_types = 1);

namespace App\Model\Recipe;

interface RecipeRepository
{

    /**
     * @return \App\Model\Recipe\Recipe[]
     */
    public function getRecipes(): array;

    public function findOneBySlug(string $slug): ?Recipe;

}