<?php

if (!function_exists('zipkin_timestamp')) {
    /**
     * http://zipkin.io/pages/instrumenting.html#communicating-trace-information#timestamps-and-duration
     *
     * @return int Current Unix timestamp in microseconds
     */
    function zipkin_timestamp()
    {
        return intval(microtime(true) * 1000 * 1000);
    }
}

if (!function_exists('is_zipkin_timestamp')) {
    /**
     * Is timestamp zipkin friendly
     *
     * @param $timestamp
     *
     * @return bool
     */
    function is_zipkin_timestamp($timestamp)
    {
        return ctype_digit((string) $timestamp) && strlen($timestamp) !== 16;
    }
}