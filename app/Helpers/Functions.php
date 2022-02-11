<?php

namespace App\Helpers;

use App\Exceptions\CustomException;

/**
 * @param $file_name
 * @return mixed|null
 */
function FileExtension($file_name)
{
    $file_extension_array=explode(".",$file_name);
    $count=count($file_extension_array);
    $count--;
    return Sis($file_extension_array["$count"]);
}

function Sanitize($mixed): ?string
{
    $result = null;
    $mixed_type = gettype($mixed);
    $valid_types = ["string", "integer", "double"];
    if (in_array($mixed_type, $valid_types))
    {
        $result = htmlentities($mixed, ENT_QUOTES, 'UTF-8');
    }
    return $result;
}

/**
 * @param null $input
 * @param null $default
 * @param array $not_be
 * @return mixed|null
 */
function Sis(&$input = null, $default = null, array $not_be=[])
{
    $ret = $default;
    if (isset($input))
    {
        if(!in_array($input,$not_be))
        {
            $ret = $input;
        }
    }
    else
    {
        unset($input);
    }
    return $ret;
}

/**
 * @param $file
 * @param array $array_allowed_format
 * @param string $direction
 * @param bool $change_file_name
 * @param bool $delete_if_duplicate
 * @return string|null
 */
function UploadIt ($file, array $array_allowed_format=['png','jpg','jpeg'], string $direction="uploads/", bool $change_file_name = true, bool $delete_if_duplicate = true): ?string
{
    $file_direction=null;
    $file_ext = FileExtension(Sis($file['name']));
    $file_ext = strtolower($file_ext);
    //var_dump_pre($_FILES['product_image']['name']);
    if(in_array($file_ext , $array_allowed_format)){
        if ($change_file_name)
        {
            $file_name=time()."_".rand(1,1000000)."_".".$file_ext";
        }
        else
        {
            $file_name = Sanitize($file['name']);
        }

        $file_direction=$direction.$file_name;

        if ($delete_if_duplicate && is_file($file_direction))
        {
            unlink($file_direction);
        }

        move_uploaded_file($file['tmp_name'],$file_direction);
    }
    return $file_direction;
}

/**
 * @param string $time
 * @return object
 */
function time_to_custom_date (string $time = 'current_time'): object
{
    if (! is_numeric($time))
    {
        $time = time();
    }

    return (object) [
        'timestamp' => $time,
        'date' => date("Y/m/j H:i:s",$time),
        'jdate' => jdate("Y/m/j H:i:s",$time),
        'string' => jdate("l j F Y",$time)
    ];
}

/**
 * @param $mixed
 * @return bool
 */
function convert_to_boolean ($mixed): bool
{
    if (is_string($mixed))
    {
        $falsy_values = [0, 'false'];
        return !in_array($mixed, $falsy_values);
    }
    else
    {
        return !empty($mixed);
    }
}

/**
 * @param array $arr
 * @return bool
 */
function isAssoc(array $arr): bool
{
    if (array() === $arr) return false;
    return array_keys($arr) !== range(0, count($arr) - 1);
}

/**
 * @param $mixed
 * @return array
 * @throws CustomException
 */
function convertToArray ($mixed): array
{
    if (is_object($mixed))
    {
        $mixed = (array) $mixed;
    }

    if (!is_array($mixed))
    {
        throw new CustomException('value should be array or object');
    }

    return $mixed;
}
