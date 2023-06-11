<?php

namespace App\Model\Recipe;

use App\Model\Tag\Tag;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\String\Slugger\AsciiSlugger;

/**
 * @ORM\Entity(repositoryClass="DefaultRecipeRepository")
 */
class Recipe
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
    private string $title;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $slug;

    /**
     * @ORM\Column(type="text")
     */
    private string $description;

    /**
     * @ORM\ManyToMany(targetEntity="App\Model\Tag\Tag", inversedBy="recipes")
     * @ORM\JoinTable(name="tagging")
     */
    private Collection $tags;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $link;

    /**
     * @ORM\Column(type="smallint")
     */
    private int $cacheBuster;

    public function __construct(
        string $title,
        string $description,
        ?string $link
    ) {
        $this->title = $title;
        $this->description = $description;
        $this->slug = (new AsciiSlugger())->slug($title)->lower();
        $this->tags = new ArrayCollection();
        $this->link = $link;
        $this->cacheBuster = 0;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function setLink(?string $link): self
    {
        $this->link = $link;

        return $this;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): void
    {
        $this->tags->add($tag);
    }

    public function getCacheBuster(): int
    {
        return $this->cacheBuster;
    }

    public function bustCache(): void
    {
        if ($this->cacheBuster >= 32767) {
            $this->cacheBuster = 0;
            return;
        }
        $this->cacheBuster++;
    }
}
