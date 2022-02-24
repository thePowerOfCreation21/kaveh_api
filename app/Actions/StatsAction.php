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
