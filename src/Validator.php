<?php

namespace Xenokore\Utility;

class Validator
{
    public static function isValidId(&$var): bool
    {
        return (isset($var) && is_numeric($var) && (int) $var > 0);
    }

    public static function isValidEmail(string $email): bool
    {
        return (filter_var($email, FILTER_VALIDATE_EMAIL) !== false);
    }

    public static function isValidUrl(string $url): bool
    {
        return (filter_var($url, FILTER_VALIDATE_URL) !== false);
    }

    public static function isValidDateTime(string $date_string, string $format = 'Y-m-d H:i:s'): bool
    {
        try {
            // See if we can create an object using the datetime format
            return (bool) \DateTime::createFromFormat($format, $datetime);
        } catch (Exception $ex) {
        }

        return false;
    }
}
