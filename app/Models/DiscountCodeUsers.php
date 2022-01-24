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
        'discount_id',
        'is_used'
    ];

    protected $casts = [
        'is_used' => 'boolean'
    ];

    public $timestamps = false;

    public function user ()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function discountCode ()
    {
        return $this->hasOne(DiscountCode::class, 'id', 'discount_id');
    }
}
