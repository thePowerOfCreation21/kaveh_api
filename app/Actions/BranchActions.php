<?php

namespace App\Actions;

use Illuminate\Http\Request;
use App\Models\Branch;
use function App\Helpers\UploadIt;

class BranchActions
{
    /**
     * insert new branch into DB
     *
     * @param Request $request
     * @return Branch
     */
    public static function store (Request $request): Branch
    {
        $fields = $request->validate([
            'title' => 'required|string|max:150',
            'description' => 'required|string|max:250',
            'address' => 'required|string|max:250',
            'image' => 'required|file|mimes:png,jpg,jpeg,gif|max:2048'
        ]);

        $fields['image'] = UploadIt($_FILES['image'], ['png', 'jpg', 'jpeg', 'gif'], 'uploads/');

        return Branch::create($fields);
    }

    public static function get (int $skip = 0, int $limit = 50)
    {
        return (object) [
            'count' => Branch::count(),
            'branches' => Branch::orderBy('id', 'DESC')->skip($skip)->take($limit)->get()
        ];
    }
}
