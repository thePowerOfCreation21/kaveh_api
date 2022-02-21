<?php

namespace App\Actions;

use App\Abstracts\Action;
use App\Exceptions\CustomException;
use App\Jobs\StoreDiscountUsers;
use App\Models\DiscountCode;
use App\Models\DiscountCodeUsers;
use App\Services\CustomResponse\CustomResponseService;
use App\Services\PaginationService;
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
            'expiration_date' => 'required|date_format:Y-m-d H:i:s'
        ],
        'check_if_user_can_use_the_discount' => [
            'code' => 'required|string|max:255'
        ],
        'get_query' => [
            'expired' => 'string|max:5'
        ],
        'get_users_query' => [
            'is_used' => 'string|max:5',
            'search' => 'string|max:150'
        ]
    ];

    protected $unusual_fields = [
        'expired' => 'boolean',
        'is_used' => 'boolean'
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
     * @param Request $request
     * @param string $discount_id
     * @param string $validation_role
     * @return object
     * @throws CustomException
     */
    public function get_users_by_request_and_discount_id (Request $request, string $discount_id, $validation_role = 'get_users_query')
    {
        $discountCode = $this->get_by_id($discount_id);

        return PaginationService::paginate_with_request(
            $request,
            $this->discount_users_query_to_eloquent(
                $discountCode,
                $this->get_data_from_request($request, $validation_role)
            )
        );
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
     * @param string $id
     * @return bool|int|null
     */
    public function delete_by_id (string $id)
    {
        DiscountCodeUsers::where('discount_id', $id)->delete();

        return DiscountCode::where('id', $id)->delete();
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

    /**
     * @param DiscountCode $discountCode
     * @param array $query
     * @param $eloquent
     * @return Model|Builder
     */
    public function discount_users_query_to_eloquent (DiscountCode $discountCode, array $query = [], $eloquent = null)
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

        if (isset($query['user_id']))
        {
            $eloquent = $eloquent->where('users.id', $query['user_id']);
        }

        return (new UserAction())->query_to_eloquent($query, $eloquent);
    }

    /**
     * @param Request $request
     * @param string|array $validation_role
     * @return CustomResponseService|null
     * @throws CustomException
     */
    public function check_if_user_can_use_discount_by_request (Request $request, $validation_role = 'check_if_user_can_use_the_discount'): ?CustomResponseService
    {
        $data = $this->get_data_from_request($request, $validation_role);

        if (!isset($data['code']))
        {
            throw new CustomException('could not get code from request', 105, 500);
        }

        $discountCode = $this->get_by_field('code', $data['code']);

        return $this->check_if_user_can_use_discount_by_discount_and_user_id(
            $discountCode,
            $this->get_user_from_request($request)->id
        );
    }

    /**
     * @param string $discount_code
     * @param string $user_id
     * @return CustomResponseService|null
     * @throws CustomException
     */
    public function check_if_user_can_be_discount_by_discount_code_and_user_id (string $discount_code, string $user_id): ?CustomResponseService
    {
        return $this->check_if_user_can_use_discount_by_discount_and_user_id(
            $this->get_by_field('code', $discount_code),
            $user_id
        );
    }

    /**
     * @param DiscountCode $discountCode
     * @param string $user_id
     * @param bool $throwException
     * @return CustomResponseService|null
     * @throws CustomException
     */
    public function check_if_user_can_use_discount_by_discount_and_user_id (DiscountCode $discountCode, string $user_id, bool $throwException = true): ?CustomResponseService
    {
        $discountUser = $this->discount_users_query_to_eloquent($discountCode, ['user_id' => $user_id])->first();

        if (empty($discountUser))
        {
            return $this->throwExceptionOrReturn(
                (new CustomResponseService())->setResult(false)
                    ->details->setProperties([
                        'code' => 1,
                        'http_code' => 400,
                        'message' => 'this user could not use this code'
                    ]),
                $throwException
            );
        }

        if ($discountCode->isExpired())
        {
            return $this->throwExceptionOrReturn(
                (new CustomResponseService())->setResult(false)
                    ->details->setProperties([
                        'code' => 2,
                        'http_code' => 400,
                        'message' => 'this discount has been expired'
                    ]),
                $throwException
            );
        }

        if ($discountUser->is_used)
        {
            return $this->throwExceptionOrReturn(
                (new CustomResponseService())->setResult(false)
                    ->details->setProperties([
                        'code' => 3,
                        'http_code' => 400,
                        'message' => 'this user already used this discount'
                    ]),
                $throwException
            );
        }

        return (new CustomResponseService())->setResult(true)
            ->details->setProperties([
                'discountCode' => $discountCode,
                'discountUser' => $discountUser
            ]);
    }

    /**
     * @param CustomResponseService $customResponse
     * @param bool $throwException
     * @return CustomResponseService
     * @throws CustomException
     */
    public function throwExceptionOrReturn (CustomResponseService $customResponse, bool $throwException = false): CustomResponseService
    {
        if ($throwException)
        {
            $customResponse->throwException();
        }
        return $customResponse;
    }

    public function used_by_user_id (DiscountCode $discountCode, string $user_id)
    {
        DiscountCodeUsers::where('discount_id', $discountCode->id)
            ->where('user_id', $user_id)
            ->delete();

        return DiscountCodeUsers::create([
            'discount_id' => $discountCode->id,
            'user_id' => $user_id,
            'is_used' => true
        ]);
    }
}
