<?php

declare(strict_types = 1);

namespace App\Model\Tag;

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
     */
    private int $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $type;

    public function __construct(
        string $name,
        string $type
    ) {
        $this->name = $name;
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

}