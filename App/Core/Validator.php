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

        public static function validate(array $data, array $rules)
        {
            $errors = [];

            foreach ($rules as $field => $ruleSet) {
                $rules = explode('|', $ruleSet);
                $value = $data[$field] ?? null;

                foreach ($rules as $rule) {
                    if ($rule === 'required' && (empty($value) && $value !== '0')) {
                        $errors[$field][] = "Поле $field обязательно для заполнения";
                        continue;
                    }

                    if (empty($value) && $value !== '0') {
                        continue; // Пропускаем проверки для необязательных пустых полей
                    }

                    if (strpos($rule, 'min:') === 0) {
                        $min = (int) str_replace('min:', '', $rule);
                        if (is_string($value) && mb_strlen($value) < $min) {
                            $errors[$field][] = "Поле $field должно содержать минимум $min символов";
                        }
                    }

                    if ($rule === 'email' && !self::email($value)) {
                        $errors[$field][] = "Поле $field должно содержать valid email";
                    }

                    // Добавляем проверку на максимальную длину
                    if (strpos($rule, 'max:') === 0) {
                        $max = (int) str_replace('max:', '', $rule);
                        if (is_string($value) && mb_strlen($value) > $max) {
                            $errors[$field][] = "Поле $field должно содержать не более $max символов";
                        }
                    }
                }
            }

            return $errors;
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

        public static function password($password)
        {
            if (strlen($password) < 10) {
                return false;
            }

            if (!preg_match('/[A-Z]/', $password)) {
                return false;
            }

            if (!preg_match('/[a-z]/', $password)) {
                return false;
            }

            if (!preg_match('/[0-9]/', $password)) {
                return false;
            }

            if (!preg_match('/[^A-Za-z0-9]/', $password)) {
                return false;
            }

            // Проверка на распространённые пароли
            $commonPasswords = ['password', '123456', 'qwerty', 'letmein', 'welcome', 'monkey'];
            if (in_array(strtolower($password), $commonPasswords)) {
                return false;
            }

            return true;
        }

        public static function sanitizeInput($input)
        {
            if (is_array($input)) {
                return array_map([self::class, 'sanitizeInput'], $input);
            }

            if (!is_string($input)) {
                return $input;
            }

            // Убираем лишние пробелы
            $clean = trim($input);
            // Удаляем непечатаемые символы
            $clean = preg_replace('/[\x00-\x1F\x7F]/u', '', $clean);
            // Экранируем специальные символы HTML
            $clean = htmlspecialchars($clean, ENT_QUOTES | ENT_HTML5, 'UTF-8');

            return $clean;
        }
    }