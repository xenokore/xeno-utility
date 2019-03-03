<?php

namespace Xenokore\Utility\Helper;

use Xenokore\Utility\Exception\JsonException;

class JsonHelper
{
    private static function _encodeDecode(string $action, $data)
    {
        $return = ('json_' . $action)($data);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new JsonException(json_last_error_msg(), json_last_error());
        }

        return $return;
    }

    public static function encode($data)
    {
        return self::_encodeDecode('encode', $data);
    }

    public static function decode($data)
    {
        return self::_encodeDecode('decode', $data);
    }

    public static function isValidJson(string $data)
    {
        if (in_array(StringHelper::subtract($data, 0, 1), ['{', '['])) {
            json_decode($string);
            return (json_last_error() === JSON_ERROR_NONE);
        }

        return false;
    }
}
