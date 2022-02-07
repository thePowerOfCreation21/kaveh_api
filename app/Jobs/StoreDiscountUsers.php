<?php

namespace App\Jobs;

use App\Actions\UserAction;
use App\Exceptions\CustomException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\DiscountCode;
use App\Models\DiscountCodeUsers;

class StoreDiscountUsers implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $discount_id = "";
    public $user_ids = [];

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $discount_id, array $user_ids)
    {
        $this->discount_id = $discount_id;
        $this->user_ids = $user_ids;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws CustomException
     */
    public function handle()
    {
        if (!DiscountCode::where('id', $this->discount_id)->exists())
        {
            throw new CustomException("discount with id '{$this->discount_id}' not found", 56, 404);
        }

        UserAction::check_if_users_exists($this->user_ids);

        foreach ($this->user_ids AS $user_id)
        {
            DiscountCodeUsers::create([
                'discount_id' => $this->discount_id,
                'user_id' => $user_id,
                'is_used' => false
            ]);
        }
    }
}
