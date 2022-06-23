<?php

namespace App\Services;

use App\Services\SendHTTPRequestService;

class SendSMSService
{
    private $api_key = "72ec72dd773a8bbfa09eac5affcb1b14225d09f49a4a15691c6b17bb3fe74423";

    protected $templates = [
        'ForgotPassword' => "شما می توانید از %param1% به عنوان رمزعبور برای ورود به پنل کاربری خود استفاده کنید
- وبسایت کاوه"
    ];

    private $line_numbers = [
        "30005006007317"
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
