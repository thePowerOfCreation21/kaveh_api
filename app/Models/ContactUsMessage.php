<?php

namespace App\Models;

use App\Casts\CustomDateCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactUsMessage extends Model
{
    use HasFactory;

    protected $table = 'contact_us_messages';

    protected $fillable = [
        'full_name',
        'email',
        'message'
    ];

    protected $hidden = [
        'updated_at'
    ];

    protected $casts = [
        'created_at' => CustomDateCast::class
    ];

    public $timestamps = false;

    public function __construct(array $attributes = [])
    {
        $this->setRawAttributes([
            'created_at' => date("Y-m-d H:i:s")
        ], true);
        parent::__construct($attributes);
    }
}
