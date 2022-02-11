<?php

namespace App\Services\CustomResponse;

use App\Exceptions\CustomException;

class CustomResponseService
{
    public $result = null;

    public $details = null;

    public function __construct()
    {
        $this->details = new Details($this);
    }

    /**
     * @param $value
     * @return $this
     */
    public function setResult ($value): CustomResponseService
    {
        $this->result = $value;
        return $this;
    }

    /**
     * @return void
     * @throws CustomException
     */
    public function throwException ()
    {
        $this->details->throwException();
    }
}
