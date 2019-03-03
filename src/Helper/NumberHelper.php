<?php

namespace Xenokore\Utility\Helper;

class NumberHelper
{
    /**
     * Converts a Belgian formatted number to a float-like string
     *
     * @param string|float|int $number
     * @return string
     */
    public static function convertFormattedNumberToFloatString($number)
    {
        if (!is_string($number) || !is_numeric(str_replace(['.', ',', '-'], '', $number))) {
            throw new \Exception('invalid number');
        }

        if (strpos($number, ',') === false && strpos($number, '.') !== false) {
            $number = str_replace('.', '', $number);
        }

        if (strpos($number, ',') !== false && strpos($number, '.') === false) {
            $number = str_replace(',', '.', $number);
        }

        if (strpos($number, ',') !== false && strpos($number, '.') !== false) {
            $number = str_replace('.', '', $number);
            $number = str_replace(',', '.', $number);
        }

        return (string) $number;
    }
}
