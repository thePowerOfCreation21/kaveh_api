<?php

namespace App\Actions;

use App\Exceptions\CustomException;
use App\Jobs\StoreDiscountUsers;
use App\Models\DiscountCode;
use App\Models\DiscountCodeUsers;
use App\Services\PaginationService;
use Illuminate\Http\Request;
use function App\Helpers\convert_to_boolean;

class DiscountCodeActions
{
    /**
     * make new discount with request
     *
     * @param Request $request
     * @return DiscountCode
     * @throws CustomException
     */
    public static function store_by_request (Request $request): DiscountCode
    {
        $discount_data = $request->validate([
            'code' => 'required|string|max:25',
            'amount' => 'required|numeric|min:1',
            'type' => 'required|in:percent,price',
            'users' => 'array|max:1000',
            'users.*' => 'distinct|numeric|min:1',
            'expiration_date' => 'date_format:Y-m-d H:i:s'
        ]);

        return self::store($discount_data);
    }

    /**
     * make new discount
     *
     * @param array $discount_data
     * @return DiscountCode
     * @throws CustomException
     */
    public static function store (array $discount_data): DiscountCode
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
            UserActions::check_if_users_exists($discount_data['users']);
        }

        $discountCode = DiscountCode::create($discount_data);

        if (!$discount_data['is_for_all_users'])
        {
            StoreDiscountUsers::dispatch($discountCode->id, $discount_data['users']);
        }

        return $discountCode;
    }

    /**
     * get discounts with request
     *
     * @param Request $request
     * @return object
     */
    public static function get_with_request (Request $request)
    {
        $query_from_request = $request->validate([
            'expired' => 'string|max:5'
        ]);

        if (isset($query_from_request['expired']))
        {
            $query_from_request['expired'] = convert_to_boolean($query_from_request['expired']);
        }

        $pagination_values = PaginationService::get_values_from_request($request);

        return self::get(array_merge($query_from_request, $pagination_values));
    }

    /**
     * get discounts
     * (has pagination)
     *
     * @param array $query
     * @return object
     */
    public static function get (array $query = [])
    {
        $discountCode = DiscountCode::orderBy('id', 'DESC');

        $query['skip'] = $query['skip'] ?? 0;
        $query['limit'] = $query['limit'] ?? 50;

        if (isset($query['expired']))
        {
            if ($query['expired'])
            {
                $discountCode = $discountCode->whereDate('expiration_date', '<=', date('Y-m-d H:i:s'));
            }
            else
            {
                $discountCode = $discountCode->whereDate('expiration_date', '>', date('Y-m-d H:i:s'));
            }
        }

        return PaginationService::paginate(
            $discountCode,
            $query['skip'],
            $query['limit']
        );
    }

    /**
     * get discount by id
     *
     * @param string $id
     * @return DiscountCode
     * @throws CustomException
     */
    public static function get_by_id (string $id): DiscountCode
    {
        $discountCode = DiscountCode::where('id', $id)->first();

        if (empty($discountCode))
        {
            throw new CustomException("could not find discount with id '{$id}'", 57, 404);
        }

        return $discountCode;
    }

    /**
     * get discount users with request
     *
     * @param Request $request
     * @param string $id
     * @return object
     */
    public static function get_users_with_request (Request $request, string $id)
    {
        $query_from_request = $request->validate([
            'is_used' => 'string|max:5',
            'search' => 'string|max:50'
        ]);

        if (isset($query_from_request['is_used']))
        {
            $query_from_request['is_used'] = convert_to_boolean($query_from_request['is_used']);
        }

        $pagination_values = PaginationService::get_values_from_request($request);

        return self::get_users($id, array_merge($query_from_request, $pagination_values));
    }

    /**
     * get discount users
     * (has pagination)
     *
     * @param string $id
     * @param array $query
     * @return object
     * @throws CustomException
     */
    public static function get_users (string $id, array $query = [])
    {
        $discountCode = self::get_by_id($id);

        $query['skip'] = $query['skip'] ?? 0;
        $query['limit'] = $query['limit'] ?? 50;

        return PaginationService::paginate(
            self::users_query_to_eloquent($discountCode, $query),
            $query['skip'],
            $query['limit']
        );
    }

    /**
     * converts query to discount users eloquent
     *
     * @param DiscountCode $discountCode
     * @param array $query
     * @param null $eloquent
     * @return \App\Models\User|null
     */
    public static function users_query_to_eloquent (DiscountCode $discountCode, array $query = [], $eloquent = null)
    {
        if ($eloquent === null)
        {
            $eloquent = $discountCode->users();
        }

        if (isset($query['is_used']))
        {
            if ($query['is_used'])
            {
                $eloquent = $eloquent->where('is_used', $query['is_used']);
            }
            else
            {
                $eloquent = $eloquent->where('is_used', "=", null)->orWhere('is_used', false);
            }
        }

        return UserActions::query_to_eloquent($query, $eloquent);
    }

    /**
     * delete discount by id
     *
     * @param string $id
     */
    public static function delete_by_id (string $id)
    {
        DiscountCode::where('id', $id)->delete();
        DiscountCodeUsers::where('discount_id', $id)->delete();
    }
}
