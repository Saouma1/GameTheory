<?php

use WP_Forge\Helpers\Arr;

if (!function_exists('data_get')) {

    /**
     * Get an item from an array or object using dot notation.
     *
     * @param mixed $data
     * @param string|array $key
     * @param mixed $default
     *
     * @return mixed
     */
    function data_get($data, $key, $default = null) {
        $segments = is_array($key) ? $key : explode('.', $key);
        foreach ($segments as $segment) {
            if (is_null($segment)) {
                return $default;
            }
            if (Arr::accessible($data) && Arr::exists($data, $segment)) {
                $data = $data[$segment];
            } elseif (is_object($data) && isset($data->{$segment})) {
                $data = $data->{$segment};
            } else {
                return $default;
            }
        }

        return $data;
    }

}

if (!function_exists('data_set')) {

    /**
     * Set an item on an array or object using dot notation.
     *
     * @param mixed $target
     * @param string|array $key
     * @param mixed $value
     * @param bool $overwrite
     * @return mixed
     */
    function data_set(&$target, $key, $value, $overwrite = true) {
        $segments = is_array($key) ? $key : explode('.', $key);
        $segment = array_shift($segments);

        if (Arr::accessible($target)) {
            if ($segments) {
                if (!Arr::exists($target, $segment)) {
                    $target[$segment] = [];
                }

                data_set($target[$segment], $segments, $value, $overwrite);
            } elseif ($overwrite || !Arr::exists($target, $segment)) {
                $target[$segment] = $value;
            }
        } elseif (is_object($target)) {
            if ($segments) {
                if (!isset($target->{$segment})) {
                    $target->{$segment} = [];
                }

                data_set($target->{$segment}, $segments, $value, $overwrite);
            } elseif ($overwrite || !isset($target->{$segment})) {
                $target->{$segment} = $value;
            }
        } else {
            $target = [];

            if ($segments) {
                data_set($target[$segment], $segments, $value, $overwrite);
            } elseif ($overwrite) {
                $target[$segment] = $value;
            }
        }

        return $target;
    }

}
