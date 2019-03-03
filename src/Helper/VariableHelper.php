<?php

namespace Xenokore\Utility\Helper;

class VariableHelper
{
    /**
     * Typecasts a given string.
     *
     * Supports: integers, doubles, bools, strings, null.
     * Anything that is not a string will simply be returned back.
     *
     * @param string $value A value that should be typecast
     * @return mixed The typecasted value
     */
    public static function typecast(string $value)
    {
        if (strtolower($value) === 'null') {
            return null;
        }

        if (strtolower($value) === 'true') {
            return true;
        }

        if (strtolower($value) === 'false') {
            return false;
        }

        if (is_numeric($value)) {
            // trick to typecast decimal numbers to doubles and normal numbers to integers
            return json_decode($value);
        }

        if (in_array(StringHelper::subtract($value, 0, 1), ['{', '['])) {
            $json = json_decode($value);
            if (json_last_error() === JSON_ERROR_NONE) {
                return (array) $json;
            }
        }

        return (string) $value;
    }
}
