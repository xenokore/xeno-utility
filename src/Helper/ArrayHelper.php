<?php
// DotNotation set & get, borrowed from Laravel : https://stackoverflow.com/a/39118759/5865844
// moveToTop, moveToBottom: https://eureka.ykyuen.info/2011/12/15/php-move-key-value-pair-to-the-topbottom-of-an-array/

namespace Xenokore\Utility\Helper;

use Xenokore\Utility\Exception\JsonException;
use Xenokore\Utility\Exception\ArrayKeyNotFoundException;

class ArrayHelper
{
    /**
     * Set an array item to a given value using "dot" notation.
     *
     * If no key is given to the method, the entire array will be replaced.
     *
     * @param  array   $array
     * @param  string  $key
     * @param  mixed   $value
     * @return array
     */
    public static function set(&$array, $key, $value)
    {
        if (is_null($key)) {
            return $array = $value;
        }

        $keys = explode('.', $key);

        while (count($keys) > 1) {
            $key = array_shift($keys);

            // If the key doesn't exist at this depth, we will just create an empty array
            // to hold the next value, allowing us to create the arrays to hold final
            // values at the correct depth. Then we'll keep digging into the array.
            if (! isset($array[$key]) || ! is_array($array[$key])) {
                $array[$key] = [];
            }

            $array = &$array[$key];
        }

        $array[array_shift($keys)] = $value;

        return $array;
    }

    /**
     * Get an item from an array using "dot" notation.
     *
     * @param  \ArrayAccess|array  $array
     * @param  string  $key
     * @param  mixed   $default
     * @param  bool    $throw   Throw an error instead of returning the default value
     * @return mixed
     */
    public static function get(array $a, string $path, $default = null, bool $throw = false)
    {
        $current = $a;
        $p       = strtok($path, '.');

        while ($p !== false) {
            if (!isset($current[$p])) {
                if ($throw) {
                    throw new ArrayKeyNotFoundException('array key not found');
                } else {
                    return $default;
                }
            }
            $current = $current[$p];
            $p       = strtok('.');
        }

        return $current;
    }

    /**
     * Determine whether the given value is array accessible.
     *
     * @param  mixed  $value
     * @return bool
     */
    public static function accessible($value)
    {
        return is_array($value) || $value instanceof ArrayAccess;
    }

    /**
     * Determine if the given key exists in the provided array.
     *
     * @param  \ArrayAccess|array  $array
     * @param  string|int  $key
     * @return bool
     */
    public static function exists($array, $key)
    {
        if ($array instanceof ArrayAccess) {
            return $array->offsetExists($key);
        }

        return array_key_exists($key, $array);
    }

    /**
     * Creates a multidimensional array based on an array of dotnotation keys
     *
     * @param array $array
     * @return array
     */
    public static function convertDotNotationToArray(array $array): array
    {
        $return_array = [];
        foreach ($array as $identifier => $value) {
            if (strpos($identifier, '.') !== false/*  && !is_array($value) */) {
                self::set($return_array, $identifier, $value);
            }
        }

        return $return_array;
    }

    /**
     * Convert multidimensional array to 2D array with dotnotation keys
     * https://stackoverflow.com/a/10424516/5865844
     *
     * @param array $array
     * @return array
     */
    public static function convertArrayToDotNotation(array $array, string $delimiter = '.'): array
    {
        //
        $it     = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($array));
        $result = [];
        foreach ($it as $leafValue) {
            $keys = [];
            foreach (range(0, $it->getDepth()) as $depth) {
                $keys[] = $it->getSubIterator($depth)->key();
            }
            $result[implode($delimiter, $keys)] = $leafValue;
        }
        return $result;
    }

    public static function moveToTop(array &$array, $key): void
    {
        if (isset($array[$key])) {
            $temp = [$key => $array[$key]];
            unset($array[$key]);
            $array = $temp + $array;
        }
    }

    public static function moveToBottom(array &$array, $key): void
    {
        if (isset($array[$key])) {
            $value = $array[$key];
            unset($array[$key]);
            $array[$key] = $value;
        }
    }

    /**
     * Merges arrays recursively and replaces distinct non-array values
     *
     * @param array $array1
     * @param array $array2
     * @return array
     * @author Daniel <daniel (at) danielsmedegaardbuus (dot) dk>
     * @author Gabriel Sobrinho <gabriel (dot) sobrinho (at) gmail (dot) com>
     */
    public static function mergeRecursiveDistinct(array $array1, array $array2): array
    {
        $merged = $array1;

        foreach ($array2 as $key => &$value) {
            if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
                $merged[$key] = self::mergeRecursiveDistinct($merged[$key], $value);
            } else {
                $merged[$key] = $value;
            }
        }
      
        return $merged;
    }

    public static function getChecksum(array $array, bool $sort = false): ?string
    {
        if (empty($array)) {
            return null;
        }

        if ($sort) {
            ksort($array);
        }

        try {
            $json = JsonHelper::decode($array);
        } catch (JsonException $e) {
            return null;
        }
        
        return StringHelper::subtract(md5($json), 0, 16);
    }
}
