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
        $request->validate([
            'skip' => 'numeric',
            'limit' => 'numeric|max:50'
        ]);

        return response()->json(
            GalleryImageActions::get_all(
                (! empty($request->input('skip'))) ? $request->input('skip') : 0,
                (! empty($request->input('limit'))) ? $request->input('limit') : 50
            )
        );
    }

    public function get_by_id (string $id)
    {
        return response()->json(GalleryImageActions::get_by_id($id));
    }

    public function update(Request $request, string $id)
    {
        GalleryImageActions::update(
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
