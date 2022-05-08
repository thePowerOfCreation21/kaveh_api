<?php

namespace App\Actions;

use App\Services\Action;
use App\Exceptions\CustomException;
use App\Models\Cart;
use App\Models\CartProduct;
use App\Models\DiscountCode;
use App\Models\DiscountCodeUsers;
use App\Models\Product;
use App\Services\PaginationService;
use Illuminate\Http\Request;
use App\Models\User;

class CartAction extends Action
{
    protected $validation_roles = [
        'store_or_update_cart_product' => [
            'quantity' => 'required|integer|max:10000'
        ]
    ];

    public function __construct()
    {
        $this->model = Cart::class;
    }

    /**
     * @param $user
     * @return User
     * @throws CustomException
     *
     */
    public function check_user ($user): User
    {
        if (empty($user))
        {
            throw new CustomException("could not get user from request", 100, 500);
        }

        if (!is_a($user, User::class))
        {
            throw new CustomException("user should be constant of ".User::class, 101, 500);
        }

        return $user;
    }

    public function empty_the_cart_by_request (Request $request)
    {
        $user = $this->check_user($request->user());

        return $this->empty_the_cart(
            (new UserAction())->get_user_cart($user)
        );
    }

    /**
     * @param Cart $cart
     * @return mixed
     */
    public function empty_the_cart (Cart $cart)
    {
        return CartProduct::where('cart_id', $cart->id)
            ->delete();
    }

    /**
     * @param Request $request
     * @param string $product_id
     * @param string|array $validation_role
     * @return CartProduct|bool|mixed|null
     * @throws CustomException
     */
    public function store_or_update_cart_product_by_request_and_product_id (
        Request $request,
        string $product_id,
        $validation_role = 'store_or_update_cart_product'
    )
    {
        $user = $this->check_user($request->user());

        return $this->store_or_update_cart_product(
            $this->get_data_from_request($request, $validation_role),
            (new ProductAction())->get_by_id($product_id),
            (new UserAction())->get_user_cart($user)
        );
    }

    /**
     * @param Request $request
     * @return object
     * @throws CustomException
     */
    public function get_cart_products_by_request (Request $request): object
    {
        return $this->get_cart_contents_by_user(
            $this->check_user($request->user())
        );
    }

    public function get_cart_total_type_by_request (Request $request)
    {
        return $this->get_cart_total_type_by_user(
            $this->get_user_from_request($request)
        );
    }

    /**
     * @param User $user
     * @return object
     */
    public function get_cart_contents_by_user (User $user): object
    {
        $cart = (new UserAction())->get_user_cart($user);
        return $this->get_cart_contents($cart);
    }

    /**
     * @param User $user
     * @return int
     */
    public function get_cart_total_type_by_user (User $user)
    {
        return $this->get_cart_total_type(
            (new UserAction())->get_user_cart($user)
        );
    }

    /**
     * @param Cart $cart
     * @return object
     */
    public function get_cart_contents (Cart $cart): object
    {
        $cart_contents = [];
        $cart_products = $cart->products;
        $total_price = 0;
        $total_quantity = 0;
        $total_type = 0;

        foreach ($cart_products AS $key => $cart_product)
        {
            $cart_contents[$key] = (object) [
                'quantity' => $cart_product->pivot->quantity,
                'amount' => $cart_product->price * $cart_product->pivot->quantity,
                'product' => $cart_product,
            ];
            unset($cart_contents[$key]->product->pivot);

            $total_price += $cart_contents[$key]->amount;
            $total_quantity += $cart_contents[$key]->quantity;
            $total_type++;
        }

        return (object) [
            'total_price' => $total_price,
            'total_quantity' => $total_quantity,
            'total_type' => $total_type,
            'cart_contents' => $cart_contents
        ];
    }

    /**
     * @param Cart $cart
     * @return int
     */
    public function get_cart_total_type (Cart $cart)
    {
        return $cart->products()->count();
    }

    /**
     * @param Request $request
     * @param string $product_id
     * @return mixed
     * @throws CustomException
     */
    public function delete_cart_product_by_request_and_product_id (Request $request, string $product_id)
    {
        $user = $this->check_user($request->user());
        $cart = (new UserAction())->get_user_cart($user);

        return CartProduct::where('cart_id', $cart->id)
            ->where('product_id', $product_id)
            ->delete();
    }

    /**
     * @param array $data
     * @param Product $product
     * @param Cart $cart
     * @return CartProduct|bool|mixed|null
     * @throws CustomException
     */
    public function store_or_update_cart_product (array $data, Product $product, Cart $cart)
    {
        $cartProduct = CartProduct::where('cart_id', $cart->id)
            ->where('product_id', $product->id)
            ->first();

        if (empty($cartProduct))
        {
            return $this->store_cart_product($data, $product, $cart);
        }

        return $this->update_cart_product($data, $cartProduct, $product);
    }

    /**
     * @param array $data
     * @param CartProduct $cartProduct
     * @param Product|null $product
     * @return CartProduct|bool|null
     * @throws CustomException
     */
    public function update_cart_product (array $data, CartProduct $cartProduct, Product $product = null)
    {
        if ($product === null || $product->id != $cartProduct->product_id)
        {
            $product = $cartProduct->product;
        }

        if ($data['quantity'] < 0)
        {
            $data['quantity'] = min($product->stock, $cartProduct->quantity + $data['quantity']);
        }
        else
        {
            $data['quantity'] = $cartProduct->quantity + $data['quantity'];
        }

        if ($data['quantity'] <= 0)
        {
            return $cartProduct->delete();
        }

        $this->check_product_stock($data['quantity'], $product);

        $cartProduct->update($data);

        return $cartProduct;
    }

    /**
     * @param array $data
     * @param Product $product
     * @param Cart $cart
     * @return mixed
     * @throws CustomException
     */
    public function store_cart_product (array $data, Product $product, Cart $cart)
    {
        if ($data['quantity'] <= 0)
        {
            throw new CustomException('trying to store product with negative or 0 quantity (product already is not in cart)', 95, 400);
        }

        $this->check_product_stock($data['quantity'], $product);

        return CartProduct::create(array_merge(
            [
                'product_id' => $product->id,
                'cart_id' => $cart->id
            ],
            $data
        ));
    }

    /**
     * @param int $quantity
     * @param Product $product
     * @return bool
     * @throws CustomException
     */
    public function check_product_stock (int $quantity, Product $product): bool
    {
        if ($quantity > 10000)
        {
            throw new CustomException('max quantity is 10,000. (even for unlimited products)', 97, 400);
        }

        if ($product->type == 'limited' && $quantity > $product->stock)
        {
            throw new CustomException('quantity value is greater than product stock', 96, 400);
        }

        return true;
    }
}
