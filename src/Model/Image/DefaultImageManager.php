<?php

namespace App\Model\Image;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class DefaultImageManager implements ImageManager
{
    public function saveImage(UploadedFile $image, string $slug): void
    {
        $image->move($this->getBasePath() . 'original/', $slug . '.jpg');
        $image = imagecreatefromjpeg($this->getBasePath() . 'original/' . $slug . '.jpg');
        $imgResized = imagescale($image, 400);
        imagejpeg($imgResized, $this->getBasePath() . 'small/' . $slug . '.jpg');
        $imgResized = imagescale($image, 1200);
        imagejpeg($imgResized, $this->getBasePath() . $slug . '.jpg');
    }

    private function getBasePath(): string
    {
        return __DIR__ . '/../../../public/images/';
    }
}
