<?php

namespace App\Jobs;

use App\Actions\UserAction;
use App\Services\SendSMSService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendSMSToUsers implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $message = "";

    protected $receptors = "*";

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $message, $receptors)
    {
        $this->receptors = $receptors;
        $this->message = $message;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $query = [];

        if (is_array($this->receptors))
        {
            $query = [
                'ids' => $this->receptors
            ];
        }

        $phone_numbers = (new UserAction())->get_phone_numbers($query);

        (new SendSMSService())->send($phone_numbers, $this->message);
    }
}
