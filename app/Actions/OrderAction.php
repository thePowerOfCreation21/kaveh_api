<?php

namespace App\Actions;

use App\Abstracts\Action;
use App\Exceptions\CustomException;
use App\Models\Cart;
use App\Models\DiscountCode;
use App\Models\Order;
use App\Models\OrderTimeLimit;
use DateTime;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class OrderAction extends Action
{
    protected $validation_roles = [
        'store' => [
            'discount_code' => 'string|max:255'
        ]
    ];

    public function __construct()
    {
        $this->model = Order::class;
    }

    public function store_by_request(Request $request, $validation_role = 'store')
    {
        $order_data = $this->get_data_from_request($request, $validation_role);

        $user = $this->get_user_from_request($request);

        $order_data['user_id'] = $user->id;

        $order_data['cart'] = (new UserAction())->get_user_cart($user);

        $order_data = $this->process_cart_contents($order_data);

        return $this->store($order_data);
    }

    /**
     * @param int $orderAmount
     * @param DiscountCode $discountCode
     * @return int
     */
    public function apply_discount_to_order_amount (int $orderAmount, DiscountCode $discountCode): int
    {
        if ($discountCode->type == 'percent')
        {
            $discountAmount = ($orderAmount / 100) * $discountCode->amount;
        }
        else
        {
            $discountAmount = $discountCode->amount;
        }

        $orderAmount -= $discountAmount;

        return max($orderAmount, 0);
    }

    /**
     * @param array $order_data
     * @return array
     * @throws CustomException
     */
    public function process_cart_contents (array $order_data): array
    {
        $order_data['amount'] = $order_data['amount'] ?? 0;
        $order_data['contents'] = $order_data['contents'] ?? [];

        if (!isset($order_data['cart']) || !is_a($order_data['cart'], Cart::class))
        {
            throw new CustomException(
                "order_data['cart'] should be set and must be instance of " . Cart::class,
                109,
                500
            );
        }

        $cart_contents = (new CartAction())->get_cart_contents($order_data['cart']);

        if (empty($cart_contents))
        {
            throw new CustomException("cart is empty", 107, 400);
        }

        $available_groups = (new OrderTimeLimit())->get_available_groups();

        $problems = [];

        foreach ($cart_contents AS $cart_content)
        {
            if (!in_array($cart_content->product->type, $available_groups))
            {
                $problems[] = [
                    'code' => 1,
                    'message' => 'could not order this product because of order time limit',
                    'product' => $cart_content->product,
                ];
                continue;
            }

            if ($cart_content->product->type == 'limited' && $cart_content->quantity > $cart_content->product->stock)
            {
                $problems[] = [
                    'code' => 2,
                    'message' => 'could not order this product because quantity is more than product stock',
                    'product' => $cart_content->product,
                ];
                continue;
            }

            $order_data['contents'][] = $cart_content;

            $order_data['amount'] += $cart_content->amount;
        }

        if (!empty($problems))
        {
            throw new CustomException(
                "something happened !",
                108,
                400,
                [
                    'problems' => $problems
                ]
            );
        }

        return $order_data;
    }

    /**
     * @param array $order_data
     * @return Model|mixed
     */
    public function store(array $order_data)
    {
        foreach ($order_data['contents'] AS $order_content)
        {
            $order_content->product->update([
                'stock' => max(($order_content->product->stock - $order_content->quantity), 0)
            ]);
        }

        if (isset($order_data['discount_code']))
        {
            $order_data['discount'] = (new DiscountCodeAction())
                ->check_if_user_can_be_discount_by_discount_code_and_user_id($order_data['discount_code'], $order_data['user_id'])
                ->details->discountCode;
            unset($order_data['discount_code']);

            $order_data['discount']->usedByUserId($order_data['user_id']);

            $order_data['amount'] = $this->apply_discount_to_order_amount($order_data['amount'], $order_data['discount']);
        }

        (new CartAction())->empty_the_cart($order_data['cart']);
        unset($order_data['cart']);

        $order_data['receipt_at'] = 'tomorrow';

        return $this->model::create($order_data);
    }
}
