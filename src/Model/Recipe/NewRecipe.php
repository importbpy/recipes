<?php

declare(strict_types = 1);

namespace App\Model\Recipe;

use Symfony\Component\HttpFoundation\File\UploadedFile;

final class NewRecipe
{

    private string $title = '';

    private string $description = '';

    private ?UploadedFile $image = null;

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }


    public function getImage(): ?UploadedFile
    {
        return $this->image;
    }

    public function setImage(UploadedFile $image): void
    {
        $this->image = $image;
    }

}