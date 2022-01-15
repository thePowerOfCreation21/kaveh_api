<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\DiscountCode;

class DiscountCodeUsers extends Model
{
    use HasFactory;

    protected $table = 'discount_code_users';

    protected $fillable = [
        'user_id',
        'discount_code',
        'is_used'
    ];

    protected $casts = [
        'is_used' => 'boolean'
    ];

    public function user ()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function discount_code ()
    {
        return $this->hasOne(DiscountCode::class, 'code', 'discount_code');
    }
}
