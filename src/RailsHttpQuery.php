<?php

namespace TechnicallyPhp;

use Traversable;

final class RailsHttpQuery
{
    /**
     * Generate URL-encoded query string
     *
     * Rails-compatible http_build_query analog.
     * Uses RFC 3986 compliant encoding (see `rawurlencode`).
     *
     * @see http://php.net/manual/en/function.http-build-query.php
     * @see http://php.net/manual/en/function.rawurlencode.php
     *
     * @param array|object $query_data
     * @param string $arg_separator
     * @return string
     */
    public static function build($query_data, $arg_separator = '&')
    {
        $chunks = [];
        foreach (self::generate($query_data) as $key => $value) {
            $chunks[] = $value !== null ? "{$key}={$value}" : $key;
        }

        return implode($arg_separator, $chunks);
    }

    private static function generate($query_data, $super_var = null)
    {
        $is_incrementing_sequence = true;
        $expected_numeric_idx = 0;

        foreach ($query_data as $key => $value) {
            if ($super_var === null) {
                // if numeric array is given on top-level, use numbers as vars
                // http_build_query(['a', 'b', 'c']) === '0=a&1=b&2=c'
                $current_var = rawurlencode((string) $key);

            } elseif ($is_incrementing_sequence && $key === $expected_numeric_idx) {
                $expected_numeric_idx++;
                // ignore 0-based incrementing numeric indexes (use index-less "[]" nesting)
                $current_var = $super_var . '[]';

            } else {
                // numeric sequence break or non-numeric key, use it explicitly
                $is_incrementing_sequence = false;
                $current_var = $super_var . '[' . rawurlencode((string) $key) . ']';
            }

            if (is_array($value) || $value instanceof Traversable || is_object($value)) {
                // nested array/object
                foreach (self::generate($value, $current_var) as $nested_var => $nested_value) {
                    yield $nested_var => $nested_value;
                }

            } else {
                // scalar value
                yield $current_var => $value !== null ? rawurlencode($value) : null;
            }
        }
    }

}
