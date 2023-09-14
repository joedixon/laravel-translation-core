<?php

namespace JoeDixon\TranslationCore;

class Arr
{
    public static function dotUsing($array, $prepend = '', $separator = '___')
    {
        $results = [];

        foreach ($array as $key => $value) {
            if (is_array($value) && ! empty($value)) {
                $results = array_merge($results, static::dotUsing($value, $prepend.$key.$separator, $separator));
            } else {
                $results[$prepend.$key] = $value;
            }
        }

        return $results;
    }

    public static function undotUsing($array, $separator = '___')
    {
        $results = [];

        foreach ($array as $key => $value) {
            static::setUsing($results, $key, $value, $separator);
            // dd($results);
        }

        return $results;
    }

    public static function setUsing(&$array, $key, $value, $separator = '___')
    {
        if (is_null($key)) {
            return $array = $value;
        }

        $keys = explode($separator, $key);

        foreach ($keys as $i => $key) {
            if (count($keys) === 1) {
                break;
            }

            unset($keys[$i]);

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
}
