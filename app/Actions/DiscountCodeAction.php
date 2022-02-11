<?php

namespace App\Actions;

use App\Abstracts\Action;
use App\Exceptions\CustomException;
use App\Jobs\StoreDiscountUsers;
use App\Models\DiscountCode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use function App\Helpers\convert_to_boolean;

class DiscountCodeAction extends Action
{
    protected $validation_roles = [
        'store' => [
            'code' => 'required|string|max:25',
            'amount' => 'required|numeric|min:1',
            'type' => 'required|in:percent,price',
            'users' => 'array|max:1000',
            'users.*' => 'distinct|numeric|min:1',
            'expiration_date' => 'date_format:Y-m-d H:i:s'
        ],
        'get_query' => [
            'expired' => 'string|max:5'
        ],
        'get_users_query' => [
            'is_used' => 'string|max:5',
            'search' => 'string|max:50'
        ]
    ];

    protected $unusual_fields = [
        'expired' => 'boolean'
    ];

    public function __construct()
    {
        $this->model = DiscountCode::class;
    }

    /**
     * @param Request $request
     * @param string|array $validation_role
     * @return Model|mixed
     * @throws CustomException
     */
    public function store_by_request (Request $request, $validation_role = 'store')
    {
        return parent::store_by_request($request, $validation_role);
    }

    /**
     * @param Request $request
     * @param string|array $query_validation_role
     * @param $eloquent
     * @param array $order_by
     * @return object
     * @throws CustomException
     */
    public function get_by_request (
        Request $request,
        $query_validation_role = 'get_query',
        $eloquent = null,
        array $order_by = ['id' => 'DESC']
    ): object
    {
        return parent::get_by_request ($request, $query_validation_role, $eloquent, $order_by);
    }

    /**
     * @param string $id
     * @return Model|mixed
     * @throws CustomException
     */
    public function get_by_id(string $id)
    {
        return parent::get_by_id($id);
    }

    /**
     * @param array $discount_data
     * @return Model
     * @throws CustomException
     */
    public function store (array $discount_data): Model
    {
        if ($discount_data['type'] == 'percent' && $discount_data['amount'] > 100)
        {
            throw new CustomException('amount of percent discount could not be more than 100', 54, 400);
        }

        if (DiscountCode::where('code', $discount_data['code'])->exists())
        {
            throw new CustomException('this code is already taken', 52, 400);
        }

        $discount_data['is_for_all_users'] = true;

        if (isset($discount_data['users']))
        {
            $discount_data['is_for_all_users'] = false;
            UserAction::check_if_users_exists($discount_data['users']);
        }

        $discountCode = DiscountCode::create($discount_data);

        if (!$discount_data['is_for_all_users'])
        {
            StoreDiscountUsers::dispatch($discountCode->id, $discount_data['users']);
        }

        return $discountCode;
    }

    /**
     * @param array $query
     * @param $eloquent
     * @return Model|Builder|null
     */
    public function query_to_eloquent(array $query, $eloquent = null)
    {
        $eloquent = parent::query_to_eloquent($query, $eloquent);

        if (isset($query['expired']))
        {
            if ($query['expired'])
            {
                $eloquent = $eloquent->whereDate('expiration_date', '<=', date('Y-m-d H:i:s'));
            }
            else
            {
                $eloquent = $eloquent->whereDate('expiration_date', '>', date('Y-m-d H:i:s'));
            }
        }

        return $eloquent;
    }
}
