<?php

namespace App\Jobs;

use App\Actions\UserActions;
use App\Exceptions\CustomException;
use App\Models\Notification;
use App\Models\NotificationUser;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class StoreNotificationUsers implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $notification_id = "";

    protected $user_ids = [];

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $notification_id, array $user_ids)
    {
        $this->notification_id = $notification_id;
        $this->user_ids = $user_ids;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (!Notification::where('id', $this->notification_id)->exists())
        {
            throw new CustomException("entity with id '{$this->notification_id}' not found", 56, 404);
        }

        UserActions::check_if_users_exists($this->user_ids);

        foreach ($this->user_ids AS $user_id)
        {
            NotificationUser::create([
                'notification_id' => $this->notification_id,
                'user_id' => $user_id,
                'is_seen' => false
            ]);
        }
    }
}
