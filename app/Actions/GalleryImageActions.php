<?php

namespace App\Actions;

use App\Models\GalleryImage;
use Illuminate\Http\Request;
use App\Exceptions\CustomException;
use App\Services\PaginationService;
use function App\Helpers\UploadIt;

class GalleryImageActions
{
    /**
     * validates request then stores the image url
     *
     * @param Request $request
     * @return GalleryImage
     * @throws CustomException
     */
    public static function store_with_request (Request $request): GalleryImage
    {
        $request->validate([
            'image' => 'required|file|mimes:png,jpg,jpeg,gif|max:2048'
        ]);

        return self::store(UploadIt($_FILES['image'], ['png', 'jpg', 'jpeg', 'gif'], 'uploads/'));
    }

    /**
     * add new image to gallery images
     *
     * @param string $image
     * @return GalleryImage
     * @throws CustomException
     */
    public static function store (string $image): GalleryImage
    {
        if (empty($image))
        {
            throw new CustomException('can not store an empty image string in db', 12, 400);
        }

        return GalleryImage::create([
            'image' => $image
        ]);
    }

    /**
     * get all images of gallery images (has pagination)
     * (uses PaginationService to paginate)
     *
     * @param Request $request
     * @return object
     */
    public static function get_all_with_request (Request $request)
    {
        return PaginationService::paginate_with_request(
            $request,
            GalleryImage::orderBy('id', 'DESC')
        );
    }

    /**
     * get image by id (returns 404 http response if id is wrong then dies)
     *
     * @param string $id
     * @return GalleryImage
     * @throws CustomException
     */
    public static function get_by_id (string $id): GalleryImage
    {
        $gallery_image = GalleryImage::where('id', $id)->first();

        if (empty($gallery_image))
        {
            throw new CustomException('could not find image with this id', 13, 404);
        }

        return $gallery_image;
    }

    /**
     * uploads image then sends image url to update method
     *
     * @param Request $request
     * @param string $id
     * @return GalleryImage
     * @throws CustomException
     */
    public static function update_with_request (Request $request, string $id)
    {
        $galleryImage = self::get_by_id($id);

        $request->validate([
            'image' => 'required|file|mimes:png,jpg,jpeg,gif|max:2048'
        ]);

        $image = UploadIt($_FILES['image'], ['png', 'jpg', 'jpeg', 'gif'], 'uploads/');

        return self::update($image, $galleryImage);
    }

    /**
     * change image by id
     * removes the old image
     *
     * @param string $image
     * @param GalleryImage $galleryImage
     * @return GalleryImage
     * @throws CustomException
     */
    public static function update (string $image, GalleryImage $galleryImage): GalleryImage
    {
        if (empty($image))
        {
            throw new CustomException('can not update image by empty string in db', 12, 400);
        }

        if (is_file($galleryImage->image))
        {
            unlink($galleryImage->image);
        }

        $galleryImage->update([
            'image' => $image
        ]);

        return $galleryImage;
    }

    /**
     * delete image by id
     * removes the image file
     *
     * @param string $id
     * @return int
     * @throws CustomException
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
