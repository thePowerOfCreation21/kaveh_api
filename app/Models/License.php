<?php

namespace App\Models;

use App\Traits\CustomModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class License extends Model
{
    use HasFactory, CustomModel;

    protected $table = 'licenses';

    protected $fillable = [
        'title',
        'image'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    protected $validation_roles = [
        'store' => [
            'title' => 'required|string|max:255',
            'image' => 'required|file|mimes:png,jpg,jpeg,gif|max:10000'
        ]
    ];

    public function before_store_or_update (array $data, Request $request): array
    {
        if (!empty($request->file('image')))
        {
            $data['image'] = $request->file('image')->store('/uploads');
        }

        return $data;
    }
}
