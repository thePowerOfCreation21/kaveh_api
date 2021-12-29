<?php

namespace App\Helpers;

function FileExtension($file_name,$input2=null)
{
    $file_extension_array=explode(".",$file_name);
    $count=count($file_extension_array);
    $count--;
    $file_extension=Sis($file_extension_array["$count"]);
    return $file_extension;
}

function Sanitize($mixed)
{
    $result = null;
    $mixed_type = \gettype($mixed);
    $valid_types = ["string", "integer", "double"];
    if (\in_array($mixed_type, $valid_types))
    {
        $result = htmlentities($mixed, ENT_QUOTES, 'UTF-8');
    }
    return $result;
}

function Sis(&$input = null, $default = null,$not_be=[])
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

function UploadIt($file, $array_allowed_format=['png','jpg','jpeg'], $direction="uploads/", $change_file_name = true, $delete_if_duplicate = true)
{
    $file_direction=null;
    $file_ext = FileExtension(Sis($file['name']));
    $file_ext = strtolower($file_ext);
    //var_dump_pre($_FILES['product_image']['name']);
    if(in_array($file_ext , $array_allowed_format)){
        if ($change_file_name)
        {
            $file_name=time()."_".rand(1,1000000)."_".base64_encode($file['name']).".$file_ext";
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

function time_to_custom_date ($time = 'current_time')
{
    if (! is_numeric($time))
    {
        $time = time();
    }

    return (object) [
        'timestamp' => $time,
        'date_time' => jdate("Y/m/j H:i:s",$time),
        'string' => jdate("l j F Y",$time)
    ];
}
