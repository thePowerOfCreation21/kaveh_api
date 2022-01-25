<?php

namespace App\Models;

use App\Pivots\DiscountUsersPivot;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Casts\CustomDateCast;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DiscountCode extends Model
{
    use HasFactory;

    protected $table = 'discount_codes';

    protected $fillable = [
        'code',
        'type',
        'amount',
        'is_for_all_users',
        'expiration_date',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'expiration_date' => CustomDateCast::class,
        'is_for_all_users' => 'boolean'
    ];

    public function users()
    {
        $eloquent = (new DiscountCodeUsers())->selectRaw("
            users.name AS user_name,
            users.last_name AS user_last_name,
            users.id AS user_id,
            IFNULL(discount_code_users.is_used, '0') AS is_used,
            IFNULL(discount_code_users.discount_id, '{$this->id}') AS discount_id
        ");

        if (!$this->is_for_all_users)
        {
            return $eloquent->join('users', function($join){
                $join->on('users.id', 'discount_code_users.user_id');
                $join->where('discount_code_users.discount_id', '=', $this->id);
            });
        }
        else
        {
            return $eloquent->rightJoin('users', function($join){
                $join->on('users.id', 'discount_code_users.user_id');
                $join->where('discount_code_users.discount_id', '=', $this->id);
            });
        }
    }
}
