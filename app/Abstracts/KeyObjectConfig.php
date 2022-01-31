<?php

namespace App\Abstracts;

use App\Exceptions\CustomException;
use App\Actions\KeyValueConfigActions;
use Illuminate\Http\Request;
use function App\Helpers\isAssoc;

abstract class KeyObjectConfig
{
    protected $key = '';

    protected $fields = [];

    protected $ignore_this_fields = [];

    protected $default_values = [];

    private $object = null;

    /**
     * cast object
     *
     * @param object $temp_object
     * @return object
     */
    public function fix_object (object $temp_object)
    {
        $object = (object) [];

        foreach ($this->fields AS $field => $validation_role)
        {
            if (in_array($field, $this->ignore_this_fields))
            {
                continue;
            }

            $object->$field = $temp_object->$field ?? ($this->default_values[$field] ?? null);
        }

        return $object;
    }

    /**
     * update object
     *
     * @param object $new_object
     * @return object
     * @throws CustomException
     */
    public function update (object $new_object)
    {
        $object = $this->get();

        $object = $this->merge_objects($object, $new_object);

        /*
        foreach ($this->fields AS $field => $validation_role)
        {
            if (in_array($field, $this->ignore_this_fields))
            {
                continue;
            }

            $object->$field = $new_object->$field ?? ($this->default_values[$field] ?? null);
        }
        */

        $object = $this->before_saving_update($object);

        KeyValueConfigActions::set($this->key, $object);

        $this->object = $object;

        return $object;
    }

    public function merge_objects (object $object_1, object $object_2, string $merge_level = "")
    {
        foreach ($object_1 AS $field => $value)
        {
            if (is_object($object_1->$field))
            {
                if (isset($object_2->$field))
                {
                    $object_1->$field = $this->merge_objects($object_1->$field, (object) $object_2->$field, "{$merge_level}{$field}.");
                }
            }
            else
            {
                $object_1->$field = $object_2->$field ?? ($this->default_values["{$merge_level}{$field}"] ?? ($this->default_values["{$merge_level}*"] ?? null));
            }
        }

        return $object_1;
    }

    /**
     * get object
     *
     * @param bool $forced_get_from_DB
     * @param bool $forced_fix_object
     * @return object
     * @throws CustomException
     */
    public function get (bool $forced_get_from_DB = false, bool $forced_fix_object = true)
    {
        $this->check_key();

        if ($forced_get_from_DB || empty($this->object))
        {
            $this->object = $this->fix_object(
                (object) KeyValueConfigActions::get($this->key)
            );
        }
        else if ($forced_fix_object)
        {
            $this->object = $this->fix_object($this->object);
        }

        return $this->object;
    }

    /**
     * checks if key is set or not
     *
     * @throws CustomException
     */
    public function check_key ()
    {
        if (empty($this->key))
        {
            throw new CustomException('KeyObjectConfig key is empty', 63, 500);
        }
    }

    /**
     * update by request
     *
     * @param Request $request
     * @return object|null
     * @throws CustomException
     */
    public function update_by_request (Request $request)
    {
        return $this->update(
            (object) $request->validate($this->fields)
        );
    }

    /**
     * it's blank but can use it on child class
     *
     * @param object $new_object
     * @return object
     */
    public function before_saving_update (object $new_object): object
    {
        return $new_object;
    }
}
