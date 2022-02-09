<?php

namespace App\Actions;

use App\Abstracts\Action;
use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class ProductAction extends Action
{
    protected $validation_roles = [
        'store' => [
            'title' => 'required|string|max:128',
            'image' => 'required|file|mimes:png,jpg,jpeg,gif|max:10000',
            'description' => 'string|max:500',
            'price' => 'required|numeric|min:1|max:1000000',
            'discount_percentage' => 'numeric|min:0|max:99',
            'type' => 'required|in:limited,unlimited',
            'stock' => 'required_if:type,==,limited|numeric|min:0|max:100000'
        ]
    ];

    protected $unusual_fields = [
        'image' => 'file'
    ];

    public function __construct()
    {
        $this->model = Product::class;
    }

    public function store_by_request(Request $request, $validation_role = 'store'): Model
    {
        return parent::store_by_request($request, $validation_role);
    }
}
