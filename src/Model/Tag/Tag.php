<?php

declare(strict_types=1);

namespace App\Model\Tag;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class Tag
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @phpstan-ignore-next-line
     */
    private int $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $name;

    /**
     * @ORM\ManyToMany(targetEntity="App\Model\Recipe\Recipe", mappedBy="tags")
     */
    private Collection $recipes;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $type;

    public function __construct(
        string $name,
        string $type
    ) {
        $this->name = $name;
        $this->recipes = new ArrayCollection();
        $this->type = $type;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getRecipes(): Collection
    {
        return $this->recipes;
    }
}
