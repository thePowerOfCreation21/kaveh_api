<?php

namespace App\Exceptions;

use Exception;
use Throwable;

class CustomException extends Exception
{
    protected $http_code = 500;

    protected $more_details = null;

    public function __construct(string $message = "", int $code = 0, int $http_code = 500, $more_details = null, Throwable $previous = null)
    {
        parent::__construct($message, $code);
        $this->http_code = $http_code;
        $this->more_details = $more_details;
    }

    /**
     * @return int
     */
    public function getHttpCode(): int
    {
        return $this->http_code;
    }

    public function render ()
    {
        $data = [
            'code' => $this->getCode(),
            'message' => $this->getMessage()
        ];

        !empty($this->more_details) && $data['more_details'] = $this->more_details;

        return response()->json($data, $this->getHttpCode());
    }
}
