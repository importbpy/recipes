<?php

namespace App\Model\Image;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class DefaultImageManager implements ImageManager
{
    public function saveImage(UploadedFile $image, string $slug): void
    {
        $image->move(__DIR__ . '/../../../public/images/original', $slug . '.jpg');

        $image = imagecreatefromjpeg(__DIR__ . '/../../../public/images/original/' . $slug . '.jpg');
        $imgResized = imagescale($image, 400);
        imagejpeg($imgResized, __DIR__ . '/../../../public/images/small/' . $slug . '.jpg');
        $imgResized = imagescale($image, 1200);
        imagejpeg($imgResized, __DIR__ . '/../../../public/images/' . $slug . '.jpg');
    }
}
