<?php

namespace App\Exceptions;

use Exception;
use Throwable;

class CustomException extends Exception
{
    protected $http_code = 500;

    public function __construct(string $message = "", int $code = 0, int $http_code = 500, Throwable $previous = null)
    {
        parent::__construct($message, $code);
        $this->http_code = $http_code;
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
        return response()->json([
            'code' => $this->getCode(),
            'message' => $this->getMessage()
        ], $this->getHttpCode());
    }
}
