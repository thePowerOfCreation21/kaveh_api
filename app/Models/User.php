<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class User extends Model
{
    use HasApiTokens, HasFactory;

    protected $table = 'users';

    protected $fillable = [
        'name',
        'last_name',
        'phone_number',
        'second_phone_number',
        'password',
        'area',
        'card_number',
        'is_blocked',
        'reason_for_blocking'
    ];

    protected $hidden = [
        'password'
    ];

    protected $casts = [
        'is_blocked' => 'boolean'
    ];

    public function format_user_data_array (array $user_data)
    {
        foreach ($this->fillable AS $field)
        {
            $user_data[$field] = $user_data[$field] ?? null;
        }

        return $user_data;
    }
}
