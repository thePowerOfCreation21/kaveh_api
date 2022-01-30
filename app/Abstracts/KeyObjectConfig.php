<?php

namespace App\Abstracts;

use App\Exceptions\CustomException;
use App\Actions\KeyValueConfigActions;
use Illuminate\Http\Request;

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

        foreach ($this->fields AS $field => $validation_role)
        {
            if (in_array($field, $this->ignore_this_fields))
            {
                continue;
            }

            $object->$field = $new_object->$field ?? ($this->default_values[$field] ?? null);
        }

        KeyValueConfigActions::set($this->key, $object);

        $this->object = $object;

        return $object;
    }

    /**
     * get object
     *
     * @param bool $forced_get_from_DB
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
}
