<?php

namespace App\Jobs;

use App\Exceptions\CustomException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\DiscountCode;
use App\Models\DiscountCodeUsers;
use App\Actions\UserActions;

class StoreDiscountUsers implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $discount_code = "";
    public $user_ids = [];

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $discount_code, array $user_ids)
    {
        $this->discount_code = $discount_code;
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
        if (!DiscountCode::where('code', $this->discount_code)->exists())
        {
            throw new CustomException("discount '{$this->discount_code}' not found", 56, 404);
        }

        UserActions::check_if_users_exists($this->user_ids);

        foreach ($this->user_ids AS $user_id)
        {
            DiscountCodeUsers::create([
                'discount_code' => $this->discount_code,
                'user_id' => $user_id,
                'is_used' => false
            ]);
        }
    }
}
