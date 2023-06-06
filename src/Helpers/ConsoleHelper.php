<?php

namespace Slides\Saml2\Helpers;

use Illuminate\Support\Arr;

/**
 * Class ConsoleHelper
 *
 * @package App\Helpers
 */
class ConsoleHelper
{
    /**
     * Convert a string like "field1:value1,field2:value" to the array
     *
     * Also supports one-dimensional array in the representation "value1,value2,value3"
     *
     * @param string|null $string
     * @param string $valueDelimiter
     * @param string $itemDelimiter
     *
     * @return array
     */
    public static function stringToArray(string $string = null, string $valueDelimiter = ':', string $itemDelimiter = ',')
    {
        if(is_null($string)) {
            return [];
        }

        $values = [];
        $items = preg_split('/' . preg_quote($itemDelimiter) . '/', $string, -1, PREG_SPLIT_NO_EMPTY);

        foreach ($items as $index => $item) {
            $item = explode($valueDelimiter, $item);

            $key = Arr::get($item, 0);
            $value = Arr::get($item, 1);

            if(is_null($value)) {
                $value = $key;
                $key = $index;
            }

            $values[trim($key)] = trim($value);
        }

        return $values;
    }

    /**
     * Converts an array to string
     *
     * ['one', 'two', 'three'] to 'one, two, three',
     * ['one' => 1, 'two' => 2, 'three' => 3] to 'one:1,two:2,three:3'
     *
     * @param array $array
     *
     * @return string
     */
    public static function arrayToString(array $array): string
    {
        $values = [];

        foreach ($array as $key => $value) {
            if(is_array($value)) {
                continue;
            }

            $values[] = is_string($key)
                ? $key . ':' . $value
                : $value;
        }

        return implode(',', $values);
    }
}