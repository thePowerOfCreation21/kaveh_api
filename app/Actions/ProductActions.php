<?php

namespace App\Actions;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Services\PaginationService;
use App\Exceptions\CustomException;
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

    /**
     * get products by request
     *
     * @param Request $request
     * @return object
     */
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
    public static function get (int $skip = 0, int $limit = 50, string $search = "", string $type = "")
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

        return PaginationService::paginate(
            $product->orderBy('id', 'DESC'),
            $skip,
            $limit
        );
    }

    /**
     * get product by id
     *
     * @param string $id
     * @return Product
     * @throws CustomException
     */
    public static function get_by_id (string $id): Product
    {
        $product = Product::where('id', $id)->first();

        if (empty($product))
        {
            throw new CustomException('could not find product with this id', 21, 404);
        }

        return $product;
    }

    /**
     * delete product by id
     *
     * @param string $id
     * @return int
     * @throws CustomException
     */
    public static function delete_by_id (string $id): int
    {
        $product = self::get_by_id($id);

        if (is_file($product->image))
        {
            unlink($product->image);
        }

        return $product->delete();
    }

    /**
     * edit product by id
     *
     * @param Request $request
     * @param string $id
     * @return Product
     * @throws CustomException
     */
    public static function edit_by_request (Request $request, string $id): Product
    {
        $product = self::get_by_id($id);

        $product_data = $request->validate([
            'title' => 'string|max:128',
            'image' => 'file|mimes:png,jpg,jpeg,gif|max:2048',
            'description' => 'string|max:500',
            'price' => 'numeric|min:1|max:1000000',
            'discount_percentage' => 'numeric|min:0|max:99',
            'stock' => 'numeric|min:0|max:100000'
        ]);

        if (isset($_FILES['image']))
        {
            if (is_file($product->image))
            {
                unlink($product->image);
            }

            $product_data['image'] = UploadIt($_FILES['image'], ['png', 'jpg', 'jpeg', 'gif'], 'uploads/');
        }

        if ($product->type == 'unlimited' && isset($product_data['stock']))
        {
            unset($product_data['stock']);
        }

        $product->update($product_data);

        return $product;

    }
}
