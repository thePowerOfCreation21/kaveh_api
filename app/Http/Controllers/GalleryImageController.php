<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Actions\GalleryImageActions;
use function App\Helpers\UploadIt;

class GalleryImageController extends Controller
{
    public function store (Request $request)
    {
        $request->validate([
            'image' => 'required|file|mimes:png,jpg,jpeg,gif|max:2048'
        ]);

        GalleryImageActions::store(UploadIt($_FILES['image'], ['png', 'jpg', 'jpeg', 'gif'], 'uploads/'));

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
        $request->validate([
            'image' => 'required|file|mimes:png,jpg,jpeg,gif|max:2048'
        ]);

        GalleryImageActions::update(
            UploadIt($_FILES['image'], ['png', 'jpg', 'jpeg', 'gif'], 'uploads/'),
            $id
        );

        return response()->json([
            'message' => 'image updated successfully'
        ]);
    }
}
