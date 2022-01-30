<?php

namespace App\Actions;

use App\Exceptions\CustomException;
use App\Services\PaginationService;
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
            'image' => 'required|file|mimes:png,jpg,jpeg,gif|max:10000'
        ]);

        $fields['image'] = $request->file('image')->store('/uploads');

        return Branch::create($fields);
    }

    /**
     * get branches from Db with pagination
     *
     * @param Request $request
     * @return object
     */
    public static function get_with_request (Request $request)
    {
        return PaginationService::paginate_with_request(
            $request,
            Branch::orderBy('id', 'DESC')
        );
    }

    /**
     * get branch by id (returns 404 http response if id is wrong then dies)
     *
     * @param string $id
     * @return Branch
     * @throws CustomException
     */
    public static function get_by_id (string $id): Branch
    {
        $branch = Branch::where('id', $id)->first();

        if (empty($branch))
        {
            throw new CustomException('could not find branch with this id', 13, 404);
        }

        return $branch;
    }

    /**
     * delete branch by id
     * removes image file
     *
     * @param string $id
     * @return bool
     * @throws CustomException
     */
    public static function delete_by_id (string $id): bool
    {
        $branch = self::get_by_id($id);

        if (is_file($branch->image))
        {
            unlink($branch->image);
        }

        return $branch->delete();
    }

    /**
     * update branch with request and id
     * only updates fields that user entered
     * deletes old image file if user edits image
     *
     * @param Request $request
     * @param string $id
     * @return Branch
     * @throws CustomException
     */
    public static function update (Request $request, string $id): Branch
    {
        $branch = self::get_by_id($id);

        $request->validate([
            'title' => 'string|max:150',
            'description' => 'string|max:250',
            'address' => 'string|max:250',
            'image' => 'file|mimes:png,jpg,jpeg,gif|max:10000'
        ]);

        $update = [];

        !empty($request->input('title')) && $update['title'] = $request->input('title');
        !empty($request->input('description')) && $update['description'] = $request->input('description');
        !empty($request->input('address')) && $update['address'] = $request->input('address');

        if (!empty($request->file('image')))
        {
            $update['image'] = $request->file('image')->store('/uploads');

            if (is_file($branch->image))
            {
                unlink($branch->image);
            }
        }

        $branch->update($update);

        return $branch;
    }
}
