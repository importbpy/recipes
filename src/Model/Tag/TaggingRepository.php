<?php

declare(strict_types = 1);

namespace App\Model\Tag;

use App\Model\Recipe\Recipe;

interface TaggingRepository
{

    public function findByTagNames(array $tagNames): array;

    public function findByRecipe(Recipe $recipe): array;

    public function delete(string $recipeId, string $tagId): void;

}