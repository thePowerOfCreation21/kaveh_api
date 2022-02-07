<?php

namespace App\Actions;

use App\Abstracts\Action;
use App\Exceptions\CustomException;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserAction extends Action
{
    protected $validation_roles = [
        'register_by_admin' => [
            'name' => 'required|string|max:64',
            'last_name' => 'required|string|max:64',
            'phone_number' => 'required|string',
            'password' => 'required|string|min:6',
            'area' => 'required|string|max:255'
        ],
        'update_by_admin' => [
            'name' => 'string|max:64',
            'last_name' => 'string|max:64',
            'phone_number' => 'regex:/09\d{9}/',
            'password' => 'string|min:6',
            'area' => 'string|max:255'
        ],
        'block_user' => [
            'reason_for_blocking' => 'required|string|max:255'
        ]
    ];

    public function __construct()
    {
        $this->model = User::class;
    }

    /**
     * @param string $id
     * @return Model
     * @throws CustomException
     */
    public function get_by_id(string $id): Model
    {
        return parent::get_by_id($id);
    }

    /**
     * @param Request $request
     * @param string $id
     * @param string|array $validation_role
     * @return Model
     * @throws CustomException
     */
    public function block_by_request_and_id (Request $request, string $id, $validation_role = 'block_user'): Model
    {
        return $this->block_by_id(
            $id,
            $this->get_data_from_request($request, $validation_role)
        );
    }

    /**
     * @param string $id
     * @param array $data
     * @return Model
     * @throws CustomException
     */
    public function block_by_id (string $id, array $data): Model
    {
        $user = $this->get_by_id($id);

        $user->update([
            'is_blocked' => true,
            'reason_for_blocking' => $data['reason_for_blocking']
        ]);

        return $user;
    }

    public function unblock_by_id (string $id): Model
    {
        $user = $this->get_by_id($id);

        $user->update([
            'is_blocked' => false,
            'reason_for_blocking' => null
        ]);

        return $user;
    }

    /**
     * @param Request $request
     * @param string|array $validation_role
     * @return Model
     * @throws CustomException
     */
    public function store_by_request(Request $request, $validation_role = 'register_by_admin'): Model
    {
        return parent::store_by_request($request, $validation_role);
    }

    /**
     * @param Request $request
     * @param string $id
     * @param string|array $validation_role
     * @return Model
     * @throws CustomException
     */
    public function update_entity_by_request_and_id(Request $request, string $id, $validation_role = 'update_by_admin'): Model
    {
        $data = $this->get_data_from_request($request, $validation_role);
        return $this->update_by_id($data, $id);
    }

    /**
     * @param array $update_data
     * @param string $id
     * @return Model
     * @throws CustomException
     */
    public function update_by_id (array $update_data, string $id)
    {
        $user = $this->get_by_id($id);

        isset($update_data['password']) && ($update_data['password'] = Hash::make($update_data['password']));

        if (isset($update_data['phone_number']))
        {
            $update_data['phone_number'] = $this->check_phone_number($update_data['phone_number']);

            if (
                User::where('id', '!=', $id)->where('phone_number', $update_data['phone_number'])->exists()
            )
            {
                throw new CustomException('this phone number is already taken', 18, 400);
            }
        }

        $user->update($update_data);

        return $user;
    }

    /**
     * @param array $data
     * @return Model
     * @throws CustomException
     */
    public function store (array $data): Model
    {
        $data['phone_number'] = $this->check_phone_number($data['phone_number']);

        if (
            User::where('phone_number', $data['phone_number'])->exists()
        )
        {
            throw new CustomException('this phone number is already taken', 17, 400);
        }

        !empty($data['password']) && $data['password'] = Hash::make($data['password']);

        return $this->model::create($data);
    }

    /**
     * @param string $phone_number
     * @return string
     * @throws CustomException
     */
    public function check_phone_number (string $phone_number): string
    {
        preg_match("/09\d{9}/", $phone_number, $phone_numbers);

        if (empty($phone_numbers))
        {
            throw new CustomException('could not match phone number with required regex pattern', 30, 400);
        }

        return $phone_numbers[0];
    }
}

