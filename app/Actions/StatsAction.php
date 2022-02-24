<?php

namespace App\Actions;

use Illuminate\Support\Facades\DB;

class StatsAction
{
    public function get ()
    {
        return collect(
            DB::select("
                SELECT
                    (
                        SELECT
                            COUNT(`id`)
                        FROM `users`
                    ) AS `users_count`,
                    (
                        SELECT
                            COUNT(`id`)
                        FROM `products`
                        WHERE
                            `deleted_at` IS NULL
                    ) AS `products_count`,
                    (
                        SELECT
                            COUNT(`id`)
                        FROM `orders`
                    ) AS `orders_count`
            ")
        )->first();
    }
}
