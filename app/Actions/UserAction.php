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
        ]
    ];

    public function __construct()
    {
        $this->model = User::class;
    }

    /**
     * @param Request $request
     * @param string $validation_role
     * @return Model
     * @throws CustomException
     */
    public function store_by_request(Request $request, $validation_role = 'register_by_admin'): Model
    {
        $data = $this->get_data_from_request($request, $validation_role);

        $data['phone_number'] = $this->check_phone_number($data['phone_number']);

        return $this->store($data);
    }

    /**
     * @param array $data
     * @return Model
     * @throws CustomException
     */
    public function store(array $data): Model
    {
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

