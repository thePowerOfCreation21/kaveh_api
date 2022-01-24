<?php

namespace App\Models;

use App\Pivots\DiscountUsersPivot;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Casts\CustomDateCast;
use App\Models\User;

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
        return $this->belongsToMany(User::class, 'discount_code_users', 'discount_id', 'user_id')
            ->using(DiscountUsersPivot::class)
            ->withPivot('is_used');
    }
}
