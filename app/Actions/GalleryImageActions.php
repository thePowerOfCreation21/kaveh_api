<?php

namespace App\Actions;

use App\Models\GalleryImage;
use Illuminate\Http\Request;
use function App\Helpers\UploadIt;

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
            'data' => GalleryImage::orderBy('id', 'DESC')->skip($skip)->take($limit)->get()
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
     * removes the old image
     *
     * @param string $image
     * @param string $id
     * @return GalleryImage
     */
    public static function update (Request $request, string $id): GalleryImage
    {
        $gallery_image = self::get_by_id($id);

        $request->validate([
            'image' => 'required|file|mimes:png,jpg,jpeg,gif|max:2048'
        ]);

        $image = UploadIt($_FILES['image'], ['png', 'jpg', 'jpeg', 'gif'], 'uploads/');

        if (empty($image))
        {
            response()->json([
                'code' => 12,
                'message' => 'can not update image by empty string in db'
            ], 400)->send();
            die();
        }

        if (is_file($gallery_image->image))
        {
            unlink($gallery_image->image);
        }

        $gallery_image->update([
            'image' => $image
        ]);

        return $gallery_image;
    }

    /**
     * delete image by id
     * removes the image file
     *
     * @param string $id
     * @return int
     */
    public static function delete (string $id): int
    {
        $gallery_image = self::get_by_id($id);

        if (is_file($gallery_image->image))
        {
            unlink($gallery_image->image);
        }

        return GalleryImage::where('id', $id)->delete();
    }
}
