<?php

namespace App\Services;

use App\Services\SendHTTPRequestService;

class SendSMSService
{
    private $api_key = "6cbf9cc295505d140c23655044fe5cdfe0e624b8e892aff8ce9b2e1bb4f8b04d";

    protected $templates = [
        'ForgotPassword' => "شما می توانید از %param1% به عنوان رمزعبور برای ورود به پنل کاربری خود استفاده کنید
- وبسایت کاوه"
    ];

    private $line_numbers = [
        "50001212124583"
    ];

    public function send ($receptor_numbers, string $message)
    {
        if (is_array($receptor_numbers))
        {
            $receptor_numbers = implode(",", $receptor_numbers);
        }

        return (new SendHTTPRequestService())->set_url("https://api.ghasedak.me/v2/sms/send/pair")
            ->set_method("POST")
            ->set_headers([
                "apikey:{$this->api_key}"
            ])
            ->set_body([
                "message" => $message,
                "receptor" => $receptor_numbers,
                "linenumber" => implode(",", $this->line_numbers)
            ])
            ->send();
    }

    public function send_otp ($receptor_numbers, $param1)
    {
        if (is_array($receptor_numbers))
        {
            $receptor_numbers = implode(",", $receptor_numbers);
        }

        return (new SendHTTPRequestService())->set_url("https://api.ghasedak.me/v2/verification/send/simple")
            ->set_headers([
                "apikey:{$this->api_key}"
            ])
            ->set_body([
                'receptor' => $receptor_numbers,
                'type' => '1',
                'template' => 'ForgotPassword',
                'param1' => $param1
            ])
            ->send();
    }
}
