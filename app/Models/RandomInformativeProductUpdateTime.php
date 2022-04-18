<?php

namespace App\Models;

use App\Abstracts\KeyObjectConfig;
use App\Exceptions\CustomException;

class RandomInformativeProductUpdateTime extends KeyObjectConfig
{
    protected $key = 'RandomInformativeProductUpdateTime';

    protected $fields = [
        'time' => 'string'
    ];

    protected $default_values = [
        'time' => null
    ];

    /**
     * @return false|int
     * @throws CustomException
     */
    public function get_time ()
    {
        $time = $this->get()->time;
        return !empty($time) ? strtotime($time) : 0;
    }

    /**
     * @return bool
     * @throws CustomException
     */
    public function is_time_to_update (): bool
    {
        return time() > ($this->get_time() + 86400);
    }
}
