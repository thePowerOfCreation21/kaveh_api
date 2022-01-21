<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Actions\GalleryImageActions;
use function App\Helpers\UploadIt;

class GalleryImageController extends Controller
{
    public function store (Request $request)
    {
        GalleryImageActions::store_with_request($request);

        return response()->json([
            'message' => 'image successfully added to gallery image(s)'
        ]);
    }

    public function get_all (Request $request)
    {
        return GalleryImageActions::get_all_with_request($request);
    }

    public function get_by_id (string $id)
    {
        return response()->json(GalleryImageActions::get_by_id($id));
    }

    public function update(Request $request, string $id)
    {
        GalleryImageActions::update_with_request(
            $request,
            $id
        );

        return response()->json([
            'message' => 'image updated successfully'
        ]);
    }

    public function delete (string $id)
    {
        GalleryImageActions::delete($id);

        return response()->json([
            'message' => 'image deleted successfully'
        ]);
    }
}
