<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Admin;

class AdminChangesHistory extends Model
{
    use HasFactory;

    protected $table = 'admin_changes_history';

    protected $fillable = [
        'doer_id',
        'subject_id',
        'action',
        'date'
    ];

    protected $casts = [
        'date' => 'object'
    ];

    public $timestamps = false;

    public function doer ()
    {
        return $this->hasOne(Admin::class, 'id', 'doer_id');
    }

    public function subject ()
    {
        return $this->hasOne(Admin::class, 'id', 'subject_id');
    }
}
