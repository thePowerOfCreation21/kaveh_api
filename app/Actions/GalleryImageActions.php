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

    /**
     * get all images of gallery images (has pagination)
     *
     * @param int $skip
     * @param int $limit
     * @return object
     */
    public static function get_all (int $skip = 0, int $limit = 50)
    {
        return (object) [
            'count' => GalleryImage::count(),
            'images' => GalleryImage::orderBy('id', 'DESC')->skip($skip)->take($limit)->get()
        ];
    }

    /**
     * get image by id (returns 404 http response if id is wrong then dies)
     *
     * @param string $id
     * @return GalleryImage
     */
    public static function get_by_id (string $id): GalleryImage
    {
        $gallery_image = GalleryImage::where('id', $id)->first();

        if (empty($gallery_image))
        {
            response()->json([
                'code' => 13,
                'message' => 'could not find image with this id'
            ], 404)->send();
            die();
        }

        return $gallery_image;
    }

    /**
     * change image by id
     *
     * @param string $image
     * @param string $id
     * @return GalleryImage
     */
    public static function update (string $image, string $id): GalleryImage
    {
        $gallery_image = self::get_by_id($id);

        if (empty($image))
        {
            response()->json([
                'code' => 12,
                'message' => 'can not update image by empty string in db'
            ], 400)->send();
            die();
        }

        $gallery_image->update([
            'image' => $image
        ]);

        return $gallery_image;
    }

    /**
     * delete image by id
     *
     * @param string $id
     * @return int
     */
    public static function delete (string $id): int
    {
        return GalleryImage::where('id', $id)->delete();
    }
}
