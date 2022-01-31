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
        ]
    ];

    public function __construct()
    {
        $this->model = License::class;
    }

    public function change_request_data_before_store_or_update (array $data, Request $request): array
    {
        if (!empty($request->file('image')))
        {
            $data['image'] = $request->file('image')->store('/uploads');
        }

        return $data;
    }
}
