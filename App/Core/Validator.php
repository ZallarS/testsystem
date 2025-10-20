<?php

    namespace App\Core;

    class Validator
    {
        private static $xssPatterns = [
            '/javascript:/i',
            '/vbscript:/i',
            '/onclick|onload|onerror|onmouse|onkey/i',
            '/<script/i',
            '/<iframe/i',
            '/<object/i',
            '/<embed/i'
        ];

        public static function sanitizeInput($input, $context = 'html')
        {
            if (is_array($input)) {
                return array_map(function($item) use ($context) {
                    return self::sanitizeInput($item, $context);
                }, $input);
            }

            if (!is_string($input)) {
                return $input;
            }

            $clean = trim($input);

            // Remove control characters
            $clean = preg_replace('/[\x00-\x1F\x7F]/u', '', $clean);

            // Context-specific sanitization
            switch ($context) {
                case 'html':
                    $clean = htmlspecialchars($clean, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    break;
                case 'sql':
                    // For SQL, we rely on prepared statements, but basic cleaning
                    $clean = preg_replace('/[\x00-\x1F\x7F]/u', '', $clean);
                    break;
                case 'js':
                    $clean = json_encode($clean, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
                    $clean = substr($clean, 1, -1); // Remove quotes
                    break;
                case 'filename':
                    $clean = preg_replace('/[^a-zA-Z0-9._-]/', '', $clean);
                    break;
                case 'email':
                    $clean = filter_var($clean, FILTER_SANITIZE_EMAIL);
                    break;
                case 'url':
                    $clean = filter_var($clean, FILTER_SANITIZE_URL);
                    break;
                default:
                    $clean = htmlspecialchars($clean, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            }

            return $clean;
        }

        public static function validateXss($input)
        {
            if (is_array($input)) {
                foreach ($input as $value) {
                    if (self::validateXss($value)) {
                        return true;
                    }
                }
                return false;
            }

            if (!is_string($input)) {
                return false;
            }

            foreach (self::$xssPatterns as $pattern) {
                if (preg_match($pattern, $input)) {
                    return true;
                }
            }

            return false;
        }

        public static function validateContext(array $data, array $rules, $context = 'default')
        {
            $errors = [];
            $sanitizedData = [];

            foreach ($rules as $field => $ruleSet) {
                $rules = explode('|', $ruleSet);
                $value = $data[$field] ?? null;

                // Sanitize based on context
                $sanitizedValue = self::sanitizeInput($value, $context);
                $sanitizedData[$field] = $sanitizedValue;

                foreach ($rules as $rule) {
                    $error = self::validateRule($field, $sanitizedValue, $rule, $context);
                    if ($error) {
                        $errors[$field][] = $error;
                    }
                }
            }

            return [$errors, $sanitizedData];
        }

        private static function validateRule($field, $value, $rule, $context)
        {
            if ($rule === 'required' && (empty($value) && $value !== '0')) {
                return "Поле $field обязательно для заполнения";
            }

            if (empty($value) && $value !== '0') {
                return null;
            }

            // XSS validation for all contexts
            if (self::validateXss($value)) {
                return "Поле $field содержит потенциально опасный контент";
            }

            switch ($rule) {
                case 'email':
                    if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        return "Поле $field должно содержать valid email";
                    }
                    break;
                case 'min:8':
                    if (strlen($value) < 8) {
                        return "Поле $field должно содержать минимум 8 символов";
                    }
                    break;
                case 'strong_password':
                    if (!self::isStrongPassword($value)) {
                        return "Пароль должен содержать заглавные и строчные буквы, цифры и специальные символы";
                    }
                    break;
            }

            return null;
        }

        private static function isStrongPassword($password)
        {
            if (strlen($password) < 10) return false;
            if (!preg_match('/[A-Z]/', $password)) return false;
            if (!preg_match('/[a-z]/', $password)) return false;
            if (!preg_match('/[0-9]/', $password)) return false;
            if (!preg_match('/[^A-Za-z0-9]/', $password)) return false;

            $commonPasswords = ['password', '123456', 'qwerty', 'letmein', 'welcome'];
            return !in_array(strtolower($password), $commonPasswords);
        }
    }