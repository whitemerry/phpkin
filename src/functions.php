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
        return ctype_digit((string) $timestamp) && strlen($timestamp) === 16;
    }
}

if (!function_exists('is_zipkin_trace_identifier')) {
    /**
     * Is zipkin trace identifier
     *
     * @param $identifier string|\whitemerry\phpkin\Identifier\Identifier
     *
     * @return bool
     */
    function is_zipkin_trace_identifier($identifier) {
        return ctype_xdigit((string) $identifier) &&
            (strlen((string) $identifier) === 16 || strlen((string) $identifier) === 32);
    }
}

if (!function_exists('is_zipkin_span_identifier')) {
    /**
     * Is zipkin span identifier
     *
     * @param $identifier string|\whitemerry\phpkin\Identifier\Identifier
     *
     * @return bool
     */
    function is_zipkin_span_identifier($identifier) {
        return ctype_xdigit((string) $identifier) && strlen((string) $identifier) === 16;
    }
}
