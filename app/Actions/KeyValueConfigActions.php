<?php

namespace App\Actions;

use App\Models\KeyValueConfig;

class KeyValueConfigActions
{
    public static function set (string $key, string $value)
    {
        KeyValueConfig::where('key', $key)->delete();
        return KeyValueConfig::create([
            'key' => $key,
            'value' => $value
        ]);
    }
}
