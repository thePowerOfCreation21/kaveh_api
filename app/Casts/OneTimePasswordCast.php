<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class OneTimePasswordCast implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return mixed
     */
    public function get($model, string $key, $value, array $attributes)
    {
        $value = json_decode($value);

        if (!is_object($value))
        {
            $value = (object) [
                'password' => '',
                'expires_at' => 1
            ];
        }

        return (object) [
            'password' => $value->password ?? '',
            'expires_at' => $value->expires_at ?? 1
        ];
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return mixed
     */
    public function set($model, string $key, $value, array $attributes)
    {
        if (is_object($value))
        {
            $value = (array) $value;
        }
        else if (!is_array($value))
        {
            $value = [
                'password' => '',
                'expires_at' => 1,
            ];
        }

        $value = [
            'password' => $value['password'] ?? '',
            'expires_at' => $value['expires_at'] ?? 1
        ];

        return json_encode($value);
    }
}
