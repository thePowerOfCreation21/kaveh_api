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
}
