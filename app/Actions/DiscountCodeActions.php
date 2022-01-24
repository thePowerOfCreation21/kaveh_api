<?php

namespace App\Actions;

use App\Exceptions\CustomException;
use App\Jobs\StoreDiscountUsers;
use App\Models\DiscountCode;
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
    public static function get (array $query)
    {
        $discountCode = new DiscountCode();

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
}
