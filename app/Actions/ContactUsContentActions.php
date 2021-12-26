<?php

namespace App\Actions;

use App\Actions\KeyValueConfigActions;
use App\Models\ContactUsContent;

class ContactUsContentActions
{
    /**
     * get object of contact us content
     *
     * @return object
     */
    public static function get ()
    {
        $contact_us_content = self::fix_object(
            (object) KeyValueConfigActions::get('contact_us_content')
        );

        return $contact_us_content;
    }

    /**
     * removes redundant fields from object & fills undefined field by null
     *
     * @param object $object
     * @return object
     */
    public static function fix_object (object $object)
    {
        $contact_us_content = (object) [];

        foreach (ContactUsContent::$fields AS $field => $validation_roles)
        {
            $contact_us_content->$field = $object->$field ?? null;
        }

        return $contact_us_content;
    }

    /**
     * update content of contact us (can only send field(s) that you want to update other fields remains the same)
     *
     * @param object $new_contact_us_content
     * @return object
     */
    public static function update (object $new_contact_us_content)
    {
        $contact_us_content = self::get();
        $new_contact_us_content = self::fix_object($new_contact_us_content);

        foreach (ContactUsContent::$fields AS $field => $validation_roles)
        {
            $contact_us_content->$field = !empty($new_contact_us_content->$field) ? $new_contact_us_content->$field : $contact_us_content->$field;
        }

        KeyValueConfigActions::set('contact_us_content', $contact_us_content);

        return $contact_us_content;
    }
}
