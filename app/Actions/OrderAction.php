<?php

namespace App\Actions;

use App\Exceptions\CustomException;
use App\Models\Cart;
use App\Models\DiscountCode;
use App\Models\Order;
use App\Models\OrderContent;
use App\Models\OrderTimeLimit;
use App\Models\Product;
use App\Services\Action;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use function App\Helpers\get_daily_time;
use function App\Helpers\Sanitize;
use function App\Helpers\str_to_daily_time;

class OrderAction extends Action
{
    protected array $validation_roles = [
        'store' => [
            'discount_code' => ['nullable', 'string', 'max:255']
        ],
        'get_query' => [
            'search' => ['string', 'min:1', 'max:150'],
            'created_at' => ['integer', 'min:1', 'max:9999999999'],
            'created_at_from' => ['integer', 'min:1', 'max:9999999999'],
            'created_at_to' => ['integer', 'min:1', 'max:9999999999'],
            /*
            'created_at' => ['date_format:Y/m/d'],
            'created_at_from' => ['date_format:Y/m/d H:i:s'],
            'created_at_to' => ['date_format:Y/m/d H:i:s'],
            */
            'product_id' => ['string', 'max:150'],
            'user_id' => ['integer', 'min:1', 'max:99999999999'],
            'todays_orders' => ['in:true,false']
        ],
        'get_todays_orders' => [
            'search' => ['string', 'min:1', 'max:150'],
            'created_at' => ['integer', 'min:1', 'max:9999999999'],
            'created_at_from' => ['integer', 'min:1', 'max:9999999999'],
            'created_at_to' => ['integer', 'min:1', 'max:9999999999'],
            /*
            'created_at' => ['date_format:Y/m/d'],
            'created_at_from' => ['date_format:Y/m/d H:i:s'],
            'created_at_to' => ['date_format:Y/m/d H:i:s'],
            */
            'product_id' => ['string', 'max:150'],
            'user_id' => ['integer', 'min:1', 'max:99999999999'],
        ],
        'get_user_orders_query' => [
            'search' => ['string', 'min:1', 'max:150'],
            'created_at' => ['integer', 'min:1', 'max:9999999999'],
            'created_at_from' => ['integer', 'min:1', 'max:9999999999'],
            'created_at_to' => ['integer', 'min:1', 'max:9999999999'],
            /*
            'created_at' => ['date_format:Y/m/d'],
            'created_at_from' => ['date_format:Y/m/d H:i:s'],
            'created_at_to' => ['date_format:Y/m/d H:i:s'],
            */
            'product_id' => ['integer', 'min:1', 'max:99999999999'],
        ],
        'get_user_product_stats' => [
            'search' => ['string', 'min:1', 'max:150'],
            'created_at' => ['integer', 'min:1', 'max:9999999999'],
            'created_at_from' => ['integer', 'min:1', 'max:9999999999'],
            'created_at_to' => ['integer', 'min:1', 'max:9999999999'],
            /*
            'created_at' => ['date_format:Y/m/d'],
            'created_at_from' => ['date_format:Y/m/d H:i:s'],
            'created_at_to' => ['date_format:Y/m/d H:i:s'],
            */
        ],
    ];

    protected array $unusual_fields = [
        'todays_orders' => 'boolean'
    ];

    public function __construct()
    {
        $this->model = Order::class;
    }

    /**
     * @param string $id
     * @param array $query
     * @param array $relations
     * @return mixed
     * @throws CustomException
     */
    public function get_by_id(string $id, array $query = [], array $relations = ['contents.product', 'user']): mixed
    {
        return parent::get_by_id($id, $query, $relations);
    }

    /**
     * @param string $id
     * @return object
     * @throws CustomException
     */
    public function get_todays_order_by_id (string $id): object
    {
        return $this->get_first_by_eloquent(
            $this->query_to_eloquent(['todays_orders' => true, 'id' => $id])
        );
    }

    /**
     * @param Request $request
     * @param array|string $validation_role
     * @param array $query_addition
     * @param object|null $eloquent
     * @param array $relations
     * @param array $order_by
     * @return object
     * @throws CustomException
     */
    public function get_by_request (Request $request, array|string $validation_role = 'get_query', array $query_addition = [], object $eloquent = null, array $relations = ['contents.product', 'user'], array $order_by = ['id' => 'DESC']): object
    {
        return parent::get_by_request($request, $validation_role, $query_addition, $eloquent, $relations, $order_by);
    }

    /**
     * @param Request $request
     * @param array|string $validation_role
     * @param array $query_addition
     * @param object|null $eloquent
     * @param array $relations
     * @param array $order_by
     * @return object
     * @throws CustomException
     */
    public function get_todays_orders_by_request (Request $request, array|string $validation_role = 'get_todays_orders', array $query_addition = [], object $eloquent = null, array $relations = ['contents.product', 'user'], array $order_by = ['id' => 'DESC']): object
    {
        return parent::get_by_request($request, $validation_role, array_merge($query_addition, ['todays_orders' => true]), $eloquent, $relations, $order_by);
    }

    /**
     * @param Request $request
     * @param string|array $validation_role
     * @return array
     * @throws CustomException
     */
    public function get_product_stats_by_request (Request $request, string|array $validation_role = 'get_query'): array
    {
        return $this->get_product_stats(
            $this->get_data_from_request($request, $validation_role)
        );
    }

    /**
     * @param Request $request
     * @param array|string $validation_role
     * @param array $query_addition
     * @param object|null $eloquent
     * @param array $relations
     * @param array $order_by
     * @return object
     * @throws CustomException
     */
    public function get_user_orders_by_request (Request $request, array|string $validation_role = 'get_query', array $query_addition = [], object $eloquent = null, array $relations = ['contents.product', 'user'], array $order_by = ['id' => 'DESC']): object
    {
        return parent::get_by_request($request, $validation_role, array_merge($query_addition, ['user_id' => $this->get_user_from_request($request)->id]), $eloquent, $relations, $order_by);
    }

    /**
     * @param Request $request
     * @param string $id
     * @return object
     * @throws CustomException
     */
    public function get_user_order_by_request_and_id (Request $request, string $id): object
    {
        return $this->get_first_by_eloquent(
            $this->query_to_eloquent(
                [
                    'user_id' => $this->get_user_from_request($request)->id,
                    'id' => $id
                ],
                relations: ['contents.product', 'user']
            )
        );
    }

    /**
     * @param Request $request
     * @param string|array $validation_role
     * @return array
     * @throws CustomException
     */
    public function get_user_product_stats_by_request (Request $request, string|array $validation_role = 'get_user_product_stats'): array
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
     * @param object|null $eloquent
     * @param array $relations
     * @param array $order_by
     * @return object|null
     * @throws CustomException
     */
    public function query_to_eloquent(array $query, object $eloquent = null, array $relations = [], array $order_by = ['id' => 'DESC']): object|null
    {
        $eloquent = parent::query_to_eloquent($query, $eloquent, $relations, $order_by);

        if (isset($query['search']))
        {
            $query['search'] = Sanitize($query['search']);
            $eloquent = $eloquent->where(function ($q) use ($query){
                $q
                    ->whereRaw("(`id`+1000) LIKE '%{$query['search']}%'")
                    ->orWhereHas('user', function($order_user_query) use ($query){
                        $order_user_query
                            ->whereRaw("(CONCAT(name, ' ', last_name)) LIKE '%{$query['search']}%'");
                    });
            });
        }

        if (isset($query['created_at']))
        {
            $eloquent = $eloquent->whereDate('created_at', '=', date('Y-m-d', $query['created_at']));
            // $eloquent = $eloquent->whereDate('created_at', '=', $query['created_at']);
        }

        if (isset($query['created_at_from']))
        {
            if (isset($query['created_at_to']))
            {
                $eloquent = $eloquent
                    ->whereDate('created_at', '>=', date("Y-m-d", $query['created_at_from']))
                    ->whereDate('created_at', '<=', date("Y-m-d", $query['created_at_to']));
                /*
                $query['created_at_from'] = explode(' ', $query['created_at_from']);
                $query['created_at_to'] = explode(' ', $query['created_at_to']);

                if ($query['created_at_from'][0] == $query['created_at_to'][0])
                {
                    $eloquent = $eloquent->where(function($q) use($query){
                        $q
                            ->whereDate('created_at', '=', $query['created_at_from'])
                            ->where(function($q) use($query){
                                $q
                                    ->whereTime('created_at', '>=', $query['created_at_from'][1])
                                    ->whereTime('created_at', '<=', $query['created_at_to'][1]);
                            });
                    });
                }
                else
                {
                    $eloquent = $eloquent
                        ->where(function($q) use($query){
                            $q
                                ->where(function($q) use($query){
                                    $q
                                        ->whereDate('created_at', '=', $query['created_at_from'][0])
                                        ->whereTime('created_at', '>=', $query['created_at_from'][1]);
                                })
                                ->orWhere(function($q) use($query){
                                    $q
                                        ->whereDate('created_at', '>', $query['created_at_from'][0])
                                        ->whereDate('created_at', '<', $query['created_at_to'][0]);
                                })
                                ->orWhere(function ($q) use($query) {
                                    $q
                                        ->whereDate('created_at', '=', $query['created_at_to'][0])
                                        ->whereTime('created_at', '<=', $query['created_at_to'][1]);
                                });
                        });
                }
                */
            }
            else
            {
                $eloquent = $eloquent->whereDate('created_at', '=', date('Y-m-d', $query['created_at_from']));
                // $eloquent = $eloquent->whereDate('created_at', '=', explode(' ', $query['created_at_from'])[0]);
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
            $current_time = get_daily_time();
            $order_time_limit = new OrderTimeLimit();

            if ($current_time > $order_time_limit->get_min_from())
            {
                /*
                dd(date('Y/m/d H:i:s', str_to_daily_time('today') + $order_time_limit->get_min_from()));
                $eloquent = $eloquent->whereTime('created_at', '>=', str_to_daily_time('today') + $order_time_limit->get_min_from());
                $eloquent = $eloquent->whereDate('created_at', '>=', date('Y-m-d', str_to_daily_time('today') + $order_time_limit->get_min_from()));
                */
                $eloquent = $eloquent->where(function ($q) use ($order_time_limit){
                    $time = str_to_daily_time('today') + $order_time_limit->get_min_from();
                    $q
                        ->whereDate('created_at', '>=', date('Y-m-d', $time))
                        ->whereTime('created_at', '>=', date('H:i:s', $time));
                });
            }
            else
            {
                $eloquent = $eloquent->where(function ($q) use ($order_time_limit){
                    $q
                        ->where(function ($q2) use ($order_time_limit){
                            $time = str_to_daily_time('today') + $order_time_limit->get_min_from();
                            $q2
                                ->whereDate('created_at', '=', date('Y-m-d', $time))
                                ->whereTime('created_at', '<=', date('H:i:s', $time));
                        })
                        ->orWhere(function ($q2) use ($order_time_limit){
                            $time = str_to_daily_time('-1 days') + $order_time_limit->get_min_from();
                            $q2
                                ->whereDate('created_at', '>=', date('Y-m-d', $time))
                                ->whereTime('created_at', '>=', date('H:i:s', $time));
                        });
                });
                /*
                $eloquent = $eloquent->whereDate('created_at', '<', date('Y-m-d', str_to_daily_time('today') + $order_time_limit->get_min_from()));
                $eloquent = $eloquent->whereDate('created_at', '>', date('Y-m-d', str_to_daily_time('-1 days') + $order_time_limit->get_min_from()));
                */
            }

            /*
            if ($current_time > min($order_time_limit->limited->from, $order_time_limit->unlimited->from) || $current_time < $order_time_limit->limited->to)
            {
                if ($current_time < min($order_time_limit->limited->from, $order_time_limit->unlimited->from))
                {
                    $eloquent = $eloquent
                        ->whereDate('created_at', '>', date('Y-m-d H:i:s', (strtotime('-1 days') + min($order_time_limit->limited->from, $order_time_limit->unlimited->from))))
                        ->whereDate('created_at', '<', date('Y-m-d H:i:s', (strtotime('today') + $order_time_limit->limited->to)));
                }
                else
                {
                    $eloquent = $eloquent
                        ->whereDate('created_at', '>', date('Y-m-d H:i:s', (strtotime('today') + min($order_time_limit->limited->from, $order_time_limit->unlimited->from))))
                        ->whereDate('created_at', '<', date('Y-m-d H:i:s', (strtotime('today') + $order_time_limit->limited->to)));
                }
            }

            if ($current_time > max($order_time_limit->limited->to, $order_time_limit->unlimited->to))
            {
                if ($order_time_limit->limited->from > $order_time_limit->limited->to || $order_time_limit->unlimited->from > $order_time_limit->unlimited->to)
                {
                    $eloquent = $eloquent
                        ->whereDate('created_at', '>', date('Y-m-d H:i:s', ( strtotime('-1 days') + min($order_time_limit->limited->from, $order_time_limit->unlimited->from))))
                        ->whereDate('created_at', '<', date('Y-m-d H:i:s', ( strtotime('today') + min($order_time_limit->limited->to, $order_time_limit->unlimited->to))));
                }
                else
                {
                    $eloquent = $eloquent->whereDate('created_at', '=', date('Y-m-d', strtotime('today')));
                }
            }
            else
            {
                if ($order_time_limit->limited->from > $order_time_limit->limited->to || $order_time_limit->unlimited->from > $order_time_limit->unlimited->to)
                {
                    $eloquent = $eloquent
                        ->whereDate('created_at', '>', date('Y-m-d H:i:s', ( strtotime('-2 days') + min($order_time_limit->limited->from, $order_time_limit->unlimited->from))))
                        ->whereDate('created_at', '<', date('Y-m-d H:i:s', ( strtotime('-1 days') + min($order_time_limit->limited->to, $order_time_limit->unlimited->to))));
                }
                else
                {
                    $eloquent = $eloquent->whereDate('created_at', '=', date('Y-m-d', strtotime('-1 days')));
                }
            }
            */
        }

        return $eloquent;
    }

    /**
     * @param Request $request
     * @param array|string $validation_role
     * @param callable|null $storing
     * @return mixed
     * @throws CustomException
     */
    public function store_by_request(Request $request, array|string $validation_role = 'store', callable $storing = null): mixed
    {
        $order_data = $this->get_data_from_request($request, $validation_role);

        $user = $this->get_user_from_request($request);

        $order_data['user_id'] = $user->id;

        $order_data['cart'] = (new UserAction())->get_user_cart($user);

        $order_data = $this->process_cart_contents($order_data);

        return $this->store($order_data, $storing);
    }

    /**
     * @param int $orderAmount
     * @param DiscountCode $discountCode
     * @return int
     */
    public function apply_discount_to_order_amount (int $orderAmount, DiscountCode $discountCode): int
    {
        if ($discountCode->getAttribute('type') == 'percent')
        {
            $discountAmount = ($orderAmount / 100) * $discountCode->getAttribute('amount');
        }
        else
        {
            $discountAmount = $discountCode->getAttribute('amount');
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

        if (empty($available_groups))
        {
            throw new CustomException('you can not order now, (because of order time limit)', 110, 400);
        }

        $problems = [];

        foreach ($cart_contents AS $cart_content)
        {
            if (!in_array($cart_content->product->type, $available_groups))
            {
                $problems[] = [
                    'code' => $cart_content->product->type == 'limited' ? 1 : 3,
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
     * @param array $data
     * @param callable|null $storing
     * @return mixed
     * @throws CustomException
     */
    public function store(array $data, callable $storing = null): mixed
    {
        if (is_callable($storing))
        {
            $storing($data);
        }

        foreach ($data['contents'] AS $order_content)
        {
            if ($order_content['product']->type == 'limited')
            {
                Product::where('id', $order_content['product']->id)->update([
                    'stock' => max(($order_content['product']->stock - $order_content['quantity']), 0)
                ]);
            }
        }

        if (isset($data['discount_code']))
        {
            $data['discount'] = (new DiscountCodeAction())
                ->check_if_user_can_be_discount_by_discount_code_and_user_id($data['discount_code'], $data['user_id'])
                ->details->discountCode;
            unset($data['discount_code']);

            $data['discount']->usedByUserId($data['user_id']);

            $data['amount'] = $this->apply_discount_to_order_amount($data['amount'], $data['discount']);
        }

        (new CartAction())->empty_the_cart($data['cart']);
        unset($data['cart']);

        $order = $this->model::create($data);

        $this->store_contents($order->id, $data['contents']);

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

    /**
     * @param object $eloquent
     * @param callable|null $deleting
     * @return mixed
     */
    public function delete_by_eloquent(object $eloquent, callable $deleting = null): mixed
    {
        $order_ids = [];
        $orderTimeLimit = (new OrderTimeLimit())->get_latest_range();
        foreach ($eloquent->with([
            'contents' => function ($q)
            {
                $q->with('product');
            }
        ])->get() AS $order)
        {
            foreach ($order->contents AS $orderContent)
            {
                if ($orderContent->product->type == 'limited' && $order->created_at->timestamp >= $orderTimeLimit['limited']['from'] && $order->created_at->timestamp <= $orderTimeLimit['limited']['to'])
                {
                    $orderContent->product->update([
                        'stock' => $orderContent->product->stock + $orderContent->quantity
                    ]);
                }
            }
            $order_ids[] = $order->id;
        }
        OrderContent::whereIn('order_id', $order_ids)->delete();
        return parent::delete_by_eloquent($eloquent, $deleting);
    }

    /**
     * @param string $id
     * @param array $query
     * @param callable|null $deleting
     * @return bool|int|null
     */
    public function delete_by_id(string $id, array $query = [], callable $deleting = null): bool|int|null
    {
        return parent::delete_by_id($id, $query, $deleting);
    }
}
