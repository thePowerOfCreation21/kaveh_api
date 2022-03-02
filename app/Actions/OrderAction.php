<?php

namespace App\Actions;

use App\Abstracts\Action;
use App\Exceptions\CustomException;
use App\Models\Cart;
use App\Models\DiscountCode;
use App\Models\Order;
use App\Models\OrderTimeLimit;
use App\Services\PaginationService;
use DateTime;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use App\Models\OrderContent;

class OrderAction extends Action
{
    protected $validation_roles = [
        'store' => [
            'discount_code' => 'string|max:255'
        ],
        'get_query' => [
            'search' => 'string|min:1|max:150',
            'created_at' => 'integer|min:1|max:9999999999',
            'created_at_from' => 'integer|min:1|max:9999999999',
            'created_at_to' => 'integer|min:1|max:9999999999',
            'product_id' => 'string|max:150',
            'user_id' => 'integer|min:1|max:99999999999',
            'todays_orders' => 'in:true,false'
        ],
        'get_todays_orders' => [
            'search' => 'string|min:1|max:150',
            'created_at' => 'integer|min:1|max:9999999999',
            'created_at_from' => 'integer|min:1|max:9999999999',
            'created_at_to' => 'integer|min:1|max:9999999999',
            'product_id' => 'string|max:150',
            'user_id' => 'integer|min:1|max:99999999999',
        ],
        'get_user_orders_query' => [
            'search' => 'string|min:1|max:150',
            'created_at' => 'integer|min:1|max:9999999999',
            'created_at_from' => 'integer|min:1|max:9999999999',
            'created_at_to' => 'integer|min:1|max:9999999999',
            'product_id' => 'integer|min:1|max:99999999999',
        ],
        'get_user_product_stats' => [
            'search' => 'string|min:1|max:150',
            'created_at' => 'integer|min:1|max:9999999999',
            'created_at_from' => 'integer|min:1|max:9999999999',
            'created_at_to' => 'integer|min:1|max:9999999999',
        ],
    ];

    protected $unusual_fields = [
        'todays_orders' => 'boolean'
    ];

    public function __construct()
    {
        $this->model = Order::class;
    }

    public function get_by_id(string $id)
    {
        $order = Order::where('id', $id)
            ->with('contents.product')
            ->with('user')
            ->first();

        if (empty($order))
        {
            throw new CustomException("order not found", 84, 404);
        }

        return $order;
    }

    /**
     * @param Request $request
     * @param string|array $query_validation_role
     * @param $eloquent
     * @param array $order_by
     * @return object
     * @throws CustomException
     */
    public function get_by_request(
        Request $request,
        $query_validation_role = 'get_query',
        $eloquent = null,
        array $order_by = ['id' => 'DESC']
    ): object
    {
        return parent::get_by_request($request, $query_validation_role, $eloquent, $order_by);
    }

    public function get_todays_orders_by_request (Request $request, $validation_role = 'get_todays_orders')
    {
        return PaginationService::paginate_with_request(
            $request,
            $this->query_to_eloquent(array_merge(['todays_orders' => true], $this->get_data_from_request($request, $validation_role)))
        );
    }

    /**
     * @param Request $request
     * @param string|array $validation_role
     * @return array
     * @throws CustomException
     */
    public function get_product_stats_by_request (Request $request, $validation_role = 'get_query'): array
    {
        return $this->get_product_stats(
            $this->get_data_from_request($request, $validation_role)
        );
    }

    /**
     * @param Request $request
     * @param string|array $validation_role
     * @return object
     * @throws CustomException
     */
    public function get_user_orders_by_request (Request $request, $validation_role = 'get_user_orders_query'): object
    {
        $user = $this->get_user_from_request($request);
        $query['user_id'] = $user->id;
        $query = array_merge($query, $this->get_data_from_request($request, $validation_role));
        return PaginationService::paginate_with_request(
            $request,
            $this->query_to_eloquent($query, null, false)
        );
    }

    /**
     * @param Request $request
     * @param string|array $validation_role
     * @return array
     * @throws CustomException
     */
    public function get_user_product_stats_by_request (Request $request, $validation_role = 'get_user_product_stats'): array
    {
        $user = $this->get_user_from_request($request);
        $query['user_id'] = $user->id;
        $query = array_merge($query, $this->get_data_from_request($request, $validation_role));
        return $this->get_product_stats($query);
    }

    /**
     * @param array $query
     * @return array
     * @throws CustomException
     */
    public function get_product_stats (array $query): array
    {
        $stats = [];
        $temp_stats = [];
        $orders = $this->query_to_eloquent($query)->get();

        foreach ($orders AS $order)
        {
            foreach ($order->contents AS $order_content)
            {
                if (empty($order_content->product) || (isset($query['product_id']) && !in_array($order_content->product->id, explode(',', $query['product_id']))))
                {
                    continue;
                }

                if (!isset($temp_stats[$order_content->product->id]))
                {
                    $temp_stats[$order_content->product->id] = [
                        'times_ordered' => 1,
                        'quantity' => $order_content['quantity'],
                        'amount' => $order_content['amount'],
                        'product' => $order_content['product'],
                    ];
                }
                else
                {
                    $temp_stats[$order_content->product->id]['times_ordered']++;
                    $temp_stats[$order_content->product->id]['quantity'] += $order_content['quantity'];
                    $temp_stats[$order_content->product->id]['amount'] += $order_content['amount'];
                }
            }
        }

        foreach ($temp_stats AS $stat)
        {
            $stats[] = $stat;
        }

        return $stats;
    }

    /**
     * @param array $query
     * @param null $eloquent
     * @param bool $with_user
     * @return Model|Builder|null
     * @throws CustomException
     */
    public function query_to_eloquent(array $query, $eloquent = null, bool $with_user = true)
    {
        $eloquent = parent::query_to_eloquent($query, $eloquent);

        $eloquent = $eloquent->with('contents.product');

        if ($with_user)
        {
            $eloquent = $eloquent->with('user');
        }

        if (isset($query['search']))
        {
            $eloquent = $eloquent->where(function ($q) use ($query){
                $q
                    ->whereRaw("(`id`+1000) LIKE '%{$query['search']}%'")
                    ->orWhereHas('contents', function($order_contents_query) use ($query){
                        $order_contents_query->whereHas('product', function($order_contents_product_query) use ($query){
                            $order_contents_product_query->where('title', 'LIKE', "%{$query['search']}%");
                        });
                    });
            });
        }

        if (isset($query['created_at']))
        {
            $eloquent = $eloquent->whereDate('created_at', '=', date('Y-m-d', $query['created_at']));
        }

        if (isset($query['created_at_from']))
        {
            if (isset($query['created_at_to']))
            {
                $eloquent = $eloquent
                    ->whereDate('created_at', '>=', date("Y-m-d", $query['created_at_from']))
                    ->whereDate('created_at', '<=', date("Y-m-d", $query['created_at_to']));
            }
            else
            {
                $eloquent = $eloquent->whereDate('created_at', '=', date('Y-m-d', $query['created_at_from']));
            }
        }

        if (isset($query['product_id']))
        {
            $product_id = explode(',', $query['product_id']);
            // $eloquent = $eloquent->whereRaw("JSON_EXTRACT(contents, '$[*].product.id') = '[{$query['product_id']}]'");
            $eloquent = $eloquent->whereHas('contents', function ($q) use ($product_id){
                $q->whereIn('product_id', $product_id);
            });
        }

        if (isset($query['user_id']))
        {
            $eloquent = $eloquent->where('user_id', $query['user_id']);
        }

        if (isset($query['todays_orders']) && $query['todays_orders'])
        {
            $today_time = time() - strtotime('today');
            $end_of_order_range = (new OrderTimeLimit())->get_end_of_order_range();

            if ($today_time > $end_of_order_range)
            {
                $eloquent = $eloquent->whereDate('created_at', '=', date('Y-m-d'));
            }
            else
            {
                $eloquent = $eloquent->whereDate('created_at', '=', date('Y-m-d', strtotime('-1 days')));
            }
        }

        return $eloquent;
    }

    /**
     * @param Request $request
     * @param string|array $validation_role
     * @return Model|mixed
     * @throws CustomException
     */
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

        $cart_contents = (new CartAction())->get_cart_contents($order_data['cart'])->cart_contents;

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

            $cart_content->product_id = $cart_content->product->id;

            $order_data['contents'][] = (array) $cart_content;

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
     * @throws CustomException
     */
    public function store(array $order_data)
    {
        foreach ($order_data['contents'] AS $order_content)
        {
            if ($order_content['product']->type == 'limited')
            {
                $order_content['product']->update([
                    'stock' => max(($order_content['product']['stock'] - $order_content['quantity']), 0)
                ]);
            }
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

        $order = $this->model::create($order_data);

        $this->store_contents($order->id, $order_data['contents']);

        return $order;
    }

    /**
     * @param string $orderId
     * @param array $contents
     * @return void
     */
    public function store_contents (string $orderId, array $contents)
    {
        foreach ($contents AS $content)
        {
            $content['order_id'] = $orderId;
            OrderContent::create($content);
        }
    }
}
