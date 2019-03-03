<?php

namespace Xenokore\Utility\Helper;

class StringHelper
{
    /**
     * Generate a random string, using a cryptographically secure
     * pseudorandom number generator (random_int)
     *
     * For PHP 7, random_int is a PHP core function
     * For PHP 5.x, depends on https://github.com/paragonie/random_compat
     *
     * @param int    $length   How many characters do we want?
     * @param string $keyspace A string of all possible characters
     *                         to select from
     * @return string
     */
    public static function generate(int $length, string $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'): string
    {
        $pieces = [];
        $max    = self::length($keyspace) - 1;
        for ($i = 0; $i < $length; ++$i) {
            $pieces [] = $keyspace[random_int(0, $max)];
        }

        return implode('', $pieces);
    }

    /**
     * Checks if a string contains a substring
     *
     * @param string $string
     * @param string $substring
     * @return boolean
     */
    public static function contains(string $string, string $substring): bool
    {
        return (strpos($string, $substring) !== false);
    }

    /**
     * Replaces specific strings in a string using an array map: ['a' => 'b']
     *
     * @param string $string
     * @param array  $array
     * @return string
     */
    public static function replace(string $string, array $array): string
    {
        return str_replace(array_keys($array), array_values($array), $string);
    }

    /**
     * Checks if a string matches a pattern (with wildcards)
     * Functions the same as fnmatch()
     *
     * @param string $string
     * @param string $pattern
     * @return boolean
     */
    public static function match(string $string, string $pattern): bool
    {
        $regex = str_replace(
            ["/", "\*", "\?"], // wildcard chars
            ['\\/', '.*', '.'],   // regexp chars
            preg_quote($pattern)
        );
     
        return preg_match('/^'.$regex.'$/is', $string);
    }

    /**
     * Checks whether or not a string starts with a given string.
     *
     * You can also pass an array of strings as the needle, which will all be checked.
     * In case one is found, this method will return true.
     *
     * @param string $string  The string to check
     * @param mixed  $needle  A string or an array of strings which should be found at the start of the string.
     *                        In case of an array, the first match will return true.
     * @param bool $case_sensitive  Whether or not the case must match (default: false)
     * @return bool If the string starts with a given string
     */
    public static function startsWith(string $string, $needle, bool $case_sensitive = false) : bool
    {
        if (!$case_sensitive) {
            $string = strtolower($string);
        }

        if (is_string($needle)) {
            if($needle === "") {
                return true;
            }
  
            if (!$case_sensitive) {
                $needle = strtolower($needle);
            }

            return strpos($string, $needle) === 0;
        }

        if (!is_array($needle)) {
            throw new \InvalidArgumentException('needle must be either a string or an array of strings');
        }
  
        foreach ($needle as $needle_point) {
            if (self::length($needle_point) < 1) {
                return true;
            }
  
            if (!$case_sensitive) {
                $needle_point = strtolower($needle_point);
            }
  
            if (self::length($needle_point) < 1 || strrpos($string, $needle_point, - self::length($string)) !== false) {
                return true;
            }
        }
  
        return false;
    }
  
    /**
     * Checks whether or not a string ends with a given string.
     *
     * You can also pass an array of strings as the needle, which will all be checked.
     * In case one is found, this method will return true.
     *
     * @param string $string  The string to check
     * @param mixed  $needle  A string or an array of strings which should be found at the end of the string.
     *                        In case of an array, the first match will return true.
     * @param bool $case_sensitive  Whether or not the case must match (default: false)
     * @return bool           If the string ends with a given string
     */
    public static function endsWith(string $string, $needle, bool $case_sensitive = false) : bool
    {
        $needles = (!is_array($needle)) ? [$needle] : $needle;
  
        if (!$case_sensitive) {
            $string = strtolower($string);
        }
  
        foreach ($needles as $needle_point) {
            if (self::length($needle_point) < 1) {
                return true;
            }
  
            if (!$case_sensitive) {
                $needle_point = strtolower($needle_point);
            }
  
            if ((($temp = self::length($string) - self::length($needle_point)) >= 0 && strpos($string, $needle_point, $temp) !== false)) {
                return true;
            }
        }
  
        return false;
    }

    // TODO: make this super dynamic: $separator = ['_', '-', ' '] or a string
    public static function camelize($input, $separator = '_')
    {
        //https://stackoverflow.com/a/33122760/5865844
        return str_replace($separator, '', ucwords(strtolower($input), $separator));
    }

    /**
     * Get the length of a string. Unicode safe.
     *
     * @param string $string
     * @return integer
     */
    public static function length(string $string): int
    {
        return (function_exists('mb_strlen')) ?
            mb_strlen($string, 'utf8') :
            strlen($string);
    }

    /**
     * Subtract characters from a string. Same as substr() but Unicode safe.
     *
     * @param string $string
     * @param integer $start
     * @param integer|null $length
     * @return string
     */
    public static function subtract(string $string, int $start = 0, ? int $length = null): string
    {
        if (is_null($length)) {
            $length = self::length($string);
        }

        return (function_exists('mb_substr ')) ?
            mb_substr($string, $start, $length, 'utf8') :
            substr($string, $start, $length);
    }
}
