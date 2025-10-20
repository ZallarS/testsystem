<?php

    namespace App\Core;

    class Validator
    {
        private $rules = [];
        private $errors = [];
        private $data = [];

        public function validate(array $data, array $rules, array $messages = [])
        {
            $this->data = $data;
            $this->errors = [];

            foreach ($rules as $field => $ruleSet) {
                $rules = explode('|', $ruleSet);

                foreach ($rules as $rule) {
                    $this->validateField($field, $rule, $messages);
                }
            }

            return $this->errors;
        }

        private function validateField($field, $rule, $messages)
        {
            $value = $this->getValue($field);
            $ruleName = $rule;
            $parameters = [];

            if (strpos($rule, ':') !== false) {
                [$ruleName, $paramString] = explode(':', $rule, 2);
                $parameters = explode(',', $paramString);
            }

            $method = 'validate' . str_replace(' ', '', ucwords(str_replace('_', ' ', $ruleName)));

            if (method_exists($this, $method)) {
                if (!$this->$method($field, $value, $parameters)) {
                    $messageKey = "{$field}.{$ruleName}";
                    $this->addError($field, $messages[$messageKey] ?? $this->getDefaultMessage($ruleName, $field, $parameters));
                }
            }
        }

        private function validateRequired($field, $value, $parameters)
        {
            return !empty($value) || $value === '0';
        }

        private function validateEmail($field, $value, $parameters)
        {
            return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
        }

        private function validateMin($field, $value, $parameters)
        {
            $min = $parameters[0] ?? 0;
            return strlen($value) >= $min;
        }

        private function validateMax($field, $value, $parameters)
        {
            $max = $parameters[0] ?? PHP_INT_MAX;
            return strlen($value) <= $max;
        }

        private function validateStrongPassword($field, $value, $parameters)
        {
            return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^\da-zA-Z]).{8,}$/', $value);
        }

        private function validateConfirmed($field, $value, $parameters)
        {
            $confirmationField = $field . '_confirmation';
            return $value === $this->getValue($confirmationField);
        }

        private function validateUnique($field, $value, $parameters)
        {
            // Реализация проверки уникальности в базе данных
            // Параметры: table,column (optional)
            $table = $parameters[0] ?? null;
            $column = $parameters[1] ?? $field;

            if (!$table) {
                return true;
            }

            // Здесь должна быть проверка в базе данных
            // Возвращаем true для примера
            return true;
        }

        private function getValue($field)
        {
            return $this->data[$field] ?? null;
        }

        private function addError($field, $message)
        {
            if (!isset($this->errors[$field])) {
                $this->errors[$field] = [];
            }

            $this->errors[$field][] = $message;
        }

        private function getDefaultMessage($rule, $field, $parameters)
        {
            $messages = [
                'required' => "The {$field} field is required.",
                'email' => "The {$field} must be a valid email address.",
                'min' => "The {$field} must be at least {$parameters[0]} characters.",
                'max' => "The {$field} may not be greater than {$parameters[0]} characters.",
                'strong_password' => "The password must contain uppercase, lowercase, numbers, and special characters.",
                'confirmed' => "The {$field} confirmation does not match.",
                'unique' => "The {$field} has already been taken."
            ];

            return $messages[$rule] ?? "The {$field} field is invalid.";
        }

        public function passes()
        {
            return empty($this->errors);
        }

        public function fails()
        {
            return !$this->passes();
        }

        public function errors()
        {
            return $this->errors;
        }
    }