<?php

namespace App\Actions;

use App\Models\GalleryImage;

class GalleryImageActions
{
    /**
     * add new image to gallery images
     *
     * @param string $image
     * @return GalleryImage
     */
    public static function store (string $image): GalleryImage
    {
        if (empty($image))
        {
            response()->json([
                'code' => 12,
                'message' => 'can not store an empty image string in db'
            ], 400)->send();
            die();
        }

        return GalleryImage::create([
            'image' => $image
        ]);
    }
}
