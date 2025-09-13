<?php

    namespace App\Core;

    class Validator
    {
        public static function string($value, $min = 1, $max = PHP_INT_MAX)
        {
            if (!is_string($value)) {
                return false;
            }
            $length = mb_strlen($value);
            return $length >= $min && $length <= $max;
        }

        public static function email($value)
        {
            return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
        }

        public static function alphanumeric($value)
        {
            return preg_match('/^[a-zA-Z0-9_]+$/', $value);
        }

        public static function integer($value, $min = null, $max = null)
        {
            if (!filter_var($value, FILTER_VALIDATE_INT)) {
                return false;
            }
            if ($min !== null && $value < $min) return false;
            if ($max !== null && $value > $max) return false;
            return true;
        }
    }