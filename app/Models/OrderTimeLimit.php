<?php

namespace App\Models;

use App\Abstracts\KeyObjectConfig;
use App\Exceptions\CustomException;

class OrderTimeLimit extends KeyObjectConfig
{
    protected $key = 'order_time_limit';

    protected $fields = [
        'limited' => 'array',
        'limited.from' => 'integer|min:1|max:86400',
        'limited.to' => 'integer|min:1|max:86400',
        'unlimited' => 'array',
        'unlimited.from' => 'integer|min:1|max:86400',
        'unlimited.to' => 'integer|min:1|max:86400',
    ];

    protected $ignore_this_fields = [
        'limited.from',
        'limited.to',
        'unlimited.from',
        'unlimited.to',
    ];

    protected $default_values = [
        'limited.from' => 0,
        'limited.to' => 1,
        'unlimited.from' => 0,
        'unlimited.to' => 1,
    ];

    /**
     * @param object $new_object
     * @return object
     * @throws CustomException
     */
    public function before_saving_update (object $new_object): object
    {
        if ($new_object->limited->to < $new_object->limited->from)
        {
            throw new CustomException("limited.to should not be less than limited.from");
        }

        if ($new_object->unlimited->to < $new_object->unlimited->from)
        {
            throw new CustomException("unlimited.to should not be less than unlimited.from");
        }

        return $new_object;
    }

    public function get_available_groups (int $time = null): array
    {
        $available_groups = [];

        if ($time === null)
        {
            $time = time() - strtotime('today');
        }

        $orderTimeLimit = $this->get();

        foreach ($orderTimeLimit as $group => $time_limit)
        {
            if ($time > $time_limit->from && $time < $time_limit->to)
            {
                $available_groups[] = $group;
            }
        }

        return $available_groups;
    }
}
