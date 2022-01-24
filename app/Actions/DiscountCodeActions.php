<?php

namespace App\Actions;

use App\Exceptions\CustomException;
use App\Jobs\StoreDiscountUsers;
use App\Models\DiscountCode;
use App\Services\PaginationService;
use Illuminate\Http\Request;

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
            StoreDiscountUsers::dispatch($discountCode->code, $discount_data['users']);
        }

        return $discountCode;
    }

    public static function get_with_request (Request $request)
    {
        $pagination_values = PaginationService::get_values_from_request($request);

        return self::get([
            'skip' => $pagination_values['skip'],
            'limit' => $pagination_values['limit'],
        ]);
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
        $query = [
            'skip' => $query['skip'] ?? 0,
            'limit' => $query['limit'] ?? 50,
        ];

        return PaginationService::paginate(
            (new DiscountCode()),
            $query['skip'],
            $query['limit']
        );
    }
}
