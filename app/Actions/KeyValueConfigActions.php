<?php

namespace App\Actions;

use App\Models\KeyValueConfig;

class KeyValueConfigActions
{
    /**
     * add new or update existing key value config
     *
     * @param string $key
     * @param string $value
     * @return KeyValueConfig
     */
    public static function set (string $key, string $value): KeyValueConfig
    {
        KeyValueConfig::where('key', $key)->delete();
        return KeyValueConfig::create([
            'key' => $key,
            'value' => $value
        ]);
    }

    /**
     * get value of specific key (returns null if couldn't find the key)
     *
     * @param string $key
     * @return KeyValueConfig|null
     */
    public static function get(string $key)
    {
        $key_value_config = KeyValueConfig::where('key', $key)->first();
        return (! empty($key_value_config)) ? $key_value_config->value : null;
    }
}
