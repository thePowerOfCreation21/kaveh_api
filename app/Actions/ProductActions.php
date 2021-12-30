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

    public static function get_by_request (Request $request)
    {
        $request->validate([
            'skip' => 'numeric|min:0',
            'limit' => 'numeric|min:0|max:50',
            'search' => 'string|max:128',
            'type' => 'in:limited,unlimited'
        ]);

        return self::get(
            !empty($request->input('skip')) ? $request->input('skip') : 0,
            !empty($request->input('limit')) ? $request->input('limit') : 50,
            (string) $request->input('search'),
            (string) $request->input('type'),
        );
    }

    /**
     * get products
     *
     * @param int $skip
     * @param int $limit
     * @param string $search
     * @param string $type
     * @return object
     */
    public static function get (int $skip = 0, $limit = 50, string $search = "", string $type = "")
    {
        $product = new Product();

        if (!empty($search))
        {
            $product = $product->where('title', 'like', "%{$search}%");
        }

        if (!empty($type))
        {
            $product = $product->where('type', $type);
        }

        return (object) [
            'count' => $product->count(),
            'data' => $product
                ->orderBy('id', 'DESC')
                ->skip($skip)
                ->take($limit)
                ->get()
        ];
    }
}
