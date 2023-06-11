<?php

namespace App\Model\Image;

use Symfony\Component\HttpFoundation\File\UploadedFile;

interface ImageManager
{
    public function saveImage(UploadedFile $image, string $slug): void;
}
