<?php

namespace App\Actions;

use App\Abstracts\Action;
use App\Models\InformativeProduct;
use Illuminate\Http\Request;

class InformativeProductAction extends Action
{
    protected $validation_roles = [
        'store' => [
            'title' => 'required|string|max:128',
            'image' => 'required|file|mimes:png,jpg,jpeg|max:10000',
            'price' => 'required|integer|min:1|max:10000000',
            'description' => 'string|max:1500'
        ],
        'get_query' => [
            'search' => 'string|max:100'
        ]
    ];

    public function __construct()
    {
        $this->model = InformativeProduct::class;
    }

    public function change_request_data_before_store_or_update(array $data, Request $request, $eloquent = null): array
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

    public function query_to_eloquent(array $query, $eloquent = null)
    {
        $eloquent = parent::query_to_eloquent($query, $eloquent);

        if (isset($query['search']))
        {
            $eloquent = $eloquent->where('title', 'LIKE', "%{$query['search']}%");
        }

        return $eloquent;
    }
}
