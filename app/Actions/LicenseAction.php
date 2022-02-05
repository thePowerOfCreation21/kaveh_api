<?php

namespace App\Actions;

use App\Abstracts\Action;
use App\Models\License;
use Illuminate\Http\Request;

class LicenseAction extends Action
{
    protected $validation_roles = [
        'store' => [
            'title' => 'required|string|max:255',
            'image' => 'required|file|mimes:png,jpg,jpeg,gif|max:10000'
        ],
        'update' => [
            'title' => 'string|max:255',
            'image' => 'file|mimes:png,jpg,jpeg,gif|max:10000'
        ]
    ];

    public function __construct()
    {
        $this->model = License::class;
    }

    public function change_request_data_before_store_or_update (array $data, Request $request, $eloquent = null): array
    {
        if (!empty($request->file('image')))
        {
            $data['image'] = $request->file('image')->store('/uploads');

            if (isset($eloquent->image) && is_file($eloquent->image))
            {
                unlink($eloquent->image);
            }
        }

        return $data;
    }

    public function delete_by_id(string $id)
    {
        $entity = $this->get_by_id($id);

        if (is_file($entity->image))
        {
            unlink($entity->image);
        }

        return $entity->delete();
    }
}