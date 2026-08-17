<?php

if (!function_exists('zipkin_timestamp')) {
    /**
     * http://zipkin.io/pages/instrumenting.html#communicating-trace-information#timestamps-and-duration
     *
     * The value has 16 digits, which does not fit in an int on 32-bit builds,
     * so it is returned as a numeric string. Treat it as opaque: subtracting
     * from it degrades to float there and stops being a valid timestamp.
     *
     * @return string Current Unix timestamp in microseconds
     */
    function zipkin_timestamp()
    {
        list($fraction, $seconds) = explode(' ', microtime());

        // microtime() always renders the fraction as "0." followed by eight
        // digits, so slicing six of them preserves the leading zeros that
        // concatenating an int would drop.
        return $seconds . substr($fraction, 2, 6);
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
