<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InformativeProduct extends Model
{
    use HasFactory;

    protected $table = 'informative_products';

    protected $fillable = [
        'title',
        'image',
        'category_id',
        'description'
    ];

    protected $hidden = [
        'is_in_index',
        'created_at',
        'updated_at'
    ];

    public static function TimedRandoms ()
    {
        if ((new RandomInformativeProductUpdateTime())->is_time_to_update())
        {
            self::updateTimedRandoms();
        }

        return self::where('is_in_index', true);
    }

    public static function updateTimedRandoms ()
    {
        self::query()->update([
            'is_in_index' => false
        ]);

        self::inRandomOrder()
            ->limit(4)
            ->update([
                'is_in_index' => true
            ]);

        (new RandomInformativeProductUpdateTime())->update((object) [
            'time' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * @return BelongsTo
     */
    public function category (): BelongsTo
    {
        return $this->belongsTo(InformativeProductCategory::class, 'category_id', 'id');
    }
}
