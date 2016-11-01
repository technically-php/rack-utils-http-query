<?php

namespace TechnicallyPhp;

use InvalidArgumentException;
use Traversable;

final class RailsHttpQuery
{
    /**
     * Generate URL-encoded query string
     *
     * Rails-compatible http_build_query analog.
     * Please not that this function uses `$enc_type = PHP_QUERY_RFC3986` as default for better interoperability.
     * This differs from default `http_build_query()` function behavior.
     *
     * @see http://php.net/manual/en/function.http-build-query.php
     *
     * @param array|object $query_data
     * @param string $arg_separator
     * @param int $enc_type
     * @return string
     */
    public static function build($query_data, $arg_separator = '&', $enc_type = PHP_QUERY_RFC3986)
    {
        if ($enc_type === PHP_QUERY_RFC1738) {
            $encode = 'urlencode';
        } elseif ($enc_type === PHP_QUERY_RFC3986) {
            $encode = 'rawurlencode';
        } else {
            throw new InvalidArgumentException('Unsupported $enc_type given. Only PHP_QUERY_RFC1738 and PHP_QUERY_RFC3986 are supported.');
        }

        $chunks = [];
        foreach (self::generate($query_data, $encode) as $key => $value) {
            $chunks[] = $key . '=' . $value;
        }

        return implode($arg_separator, $chunks);
    }

    private function generate($query_data, $super_var = null, callable $encode)
    {
        $is_incrementing_sequence = true;
        $expected_numeric_idx = 0;

        foreach ($query_data as $key => $value) {
            if ($super_var === null) {
                // if numeric array is given on top-level, use numbers as vars
                // http_build_query(['a', 'b', 'c']) === '0=a&1=b&2=c'
                $current_var = $encode((string) $key);

            } elseif ($is_incrementing_sequence && $key === $expected_numeric_idx) {
                $expected_numeric_idx++;
                // ignore 0-based incrementing numeric indexes (use index-less "[]" nesting)
                $current_var = $super_var . '[]';

            } else {
                // numeric sequence break or non-numeric key, use it explicitly
                $is_incrementing_sequence = false;
                $current_var = $super_var . '[' . $encode((string) $key) . ']';
            }

            if (is_array($value) || $value instanceof Traversable || is_object($value)) {
                // nested array/object
                foreach (self::generate($value, $current_var) as $nested_var => $nested_value) {
                    yield $nested_var => $nested_value;
                }

            } else {
                // scalar value
                yield $current_var => $value;
            }
        }
    }

}