<?php

declare(strict_types = 1);

namespace App\Model\Tag;

use App\Model\Recipe\Recipe;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class Tagging
{

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Model\Recipe\Recipe")
     */
    private Recipe $recipe;

    /**
     * @ORM\ManyToOne(targetEntity="App\Model\Tag\Tag")
     */
    private Tag $tag;

    public function __construct(
        Recipe $recipe,
        Tag $tag
    )
    {
        $this->recipe = $recipe;
        $this->tag = $tag;
    }

    public function getRecipe(): Recipe
    {
        return $this->recipe;
    }

    public function getTag(): Tag
    {
        return $this->tag;
    }
}