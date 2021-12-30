<?php

namespace App\Actions;

use Illuminate\Http\Request;
use App\Models\Product;
use function App\Helpers\UploadIt;

class ProductActions
{
    /**
     * add new product by request
     *
     * @param Request $request
     * @return Product
     */
    public static function store_by_request (Request $request): Product
    {
        $product_data = $request->validate([
            'title' => 'required|string|max:128',
            'image' => 'required|file|mimes:png,jpg,jpeg,gif|max:2048',
            'description' => 'string|max:500',
            'price' => 'required|numeric|min:1|max:1000000',
            'discount_percentage' => 'numeric|min:0|max:99',
            'type' => 'required|in:limited,unlimited',
            'stock' => 'required_if:type,==,limited|numeric|min:0|max:100000'
        ]);
        $product_data['image'] = UploadIt($_FILES['image'], ['png', 'jpg', 'jpeg', 'gif'], 'uploads/');

        return Product::create($product_data);
    }
}
