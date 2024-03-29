<?php

namespace App\Models;

use App\Services\KeyObjectConfig;
use App\Exceptions\CustomException;
use function App\Helpers\get_daily_time;

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
        'limited' => [
            'from' => 0,
            'to' => 1
        ],
        'unlimited' => [
            'from' => 0,
            'to' => 1
        ],
        'limited.from' => 0,
        'limited.to' => 1,
        'unlimited.from' => 0,
        'unlimited.to' => 1,
    ];

    /**
     * @param object $new_object
     * @return object
     */
    public function before_saving_update (object $new_object): object
    {
        $new_object->limited = (object) $new_object->limited;
        $new_object->unlimited = (object) $new_object->unlimited;

        /*
        if ($new_object->limited->to < $new_object->limited->from)
        {
            throw new CustomException("limited.to should not be less than limited.from");
        }

        if ($new_object->unlimited->to < $new_object->unlimited->from)
        {
            throw new CustomException("unlimited.to should not be less than unlimited.from");
        }
        */

        return $new_object;
    }

    /**
     * @param int|null $time
     * @return array
     * @throws CustomException
     */
    public function get_available_groups (int $time = null): array
    {
        $available_groups = [];

        if ($time === null)
        {
            $time = get_daily_time();
        }

        $orderTimeLimit = $this->get();

        foreach ($orderTimeLimit as $group => $time_limit)
        {
            if ($time_limit->from > $time_limit->to && ($time > $time_limit->from || $time < $time_limit->to))
            {
                $available_groups[] = $group;
            }
            else if ($time > $time_limit->from && $time < $time_limit->to)
            {
                $available_groups[] = $group;
            }
        }

        return $available_groups;
    }

    /**
     * @param bool $forced_get_from_DB
     * @param bool $forced_fix_object
     * @return object|null
     * @throws CustomException
     */
    public function get (bool $forced_get_from_DB = false, bool $forced_fix_object = true): ?object
    {
        $orderTimeLimit =  parent::get($forced_get_from_DB, $forced_fix_object);

        $orderTimeLimit->limited = (object) $orderTimeLimit->limited;
        $orderTimeLimit->unlimited = (object) $orderTimeLimit->unlimited;

        return $orderTimeLimit;
    }

    /**
     * @return mixed
     * @throws CustomException
     */
    public function get_min_from ()
    {
        $orderTimeLimit = $this->get();
        return min($orderTimeLimit->limited->from, $orderTimeLimit->unlimited->from);
    }

    /**
     * @return mixed
     * @throws CustomException
     */
    public function get_max_from ()
    {
        $orderTimeLimit = $this->get();
        return max($orderTimeLimit->limited->from, $orderTimeLimit->unlimited->from);
    }

    /**
     * @return mixed
     * @throws CustomException
     */
    public function get_min_to ()
    {
        $orderTimeLimit = $this->get();
        return min($orderTimeLimit->limited->to, $orderTimeLimit->unlimited->to);
    }

    /**
     * @return mixed
     * @throws CustomException
     */
    public function get_max_to ()
    {
        $orderTimeLimit = $this->get();
        return max($orderTimeLimit->limited->to, $orderTimeLimit->unlimited->to);
    }

    /**
     * @return mixed
     * @throws CustomException
     */
    public function get_end_of_order_range ()
    {
        $orderTimeLimit = $this->get();
        return max($orderTimeLimit->limited->to, $orderTimeLimit->unlimited->to);
    }

    public function get_latest_range ()
    {
        $result = [
            'limited' => [
                'from' => 0,
                'to' => 0
            ],
            'unlimited' => [
                'from' => 0,
                'to' => 0
            ]
        ];
        $orderTimeLimit = $this->get();

        if ($orderTimeLimit->limited->from > $orderTimeLimit->limited->to)
        {
            $result['limited'] = [
                'from' => strtotime('-1 days') + $orderTimeLimit->limited->from,
                'to' => strtotime('today') + $orderTimeLimit->limited->to
            ];
        }
        else
        {
            $result['limited'] = [
                'from' => strtotime('today') + $orderTimeLimit->limited->from,
                'to' => strtotime('today') + $orderTimeLimit->limited->to
            ];
        }

        if ($orderTimeLimit->unlimited->from > $orderTimeLimit->unlimited->to)
        {
            $result['unlimited'] = [
                'from' => strtotime('-1 days') + $orderTimeLimit->unlimited->from,
                'to' => strtotime('today') + $orderTimeLimit->unlimited->to
            ];
        }
        else
        {
            $result['unlimited'] = [
                'from' => strtotime('today') + $orderTimeLimit->unlimited->from,
                'to' => strtotime('today') + $orderTimeLimit->unlimited->to
            ];
        }

        return $result;
    }
}
