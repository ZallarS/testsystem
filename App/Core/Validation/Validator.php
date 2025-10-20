<?php

    namespace App\Core\Validation;

    class Validator
    {
        private $data;
        private $rules;
        private $errors = [];
        private $customMessages = [];

        public function __construct(array $data, array $rules, array $messages = [])
        {
            $this->data = $data;
            $this->rules = $rules;
            $this->customMessages = $messages;
        }

        public function validate()
        {
            foreach ($this->rules as $attribute => $rules) {
                $rules = $this->parseRules($rules);
                $value = $this->getValue($attribute);

                foreach ($rules as $rule) {
                    if (!$rule->passes($attribute, $value)) {
                        $this->addError($attribute, $rule->message($attribute));
                        break;
                    }
                }
            }

            return $this->errors;
        }

        public function passes()
        {
            return empty($this->validate());
        }

        public function fails()
        {
            return !$this->passes();
        }

        public function errors()
        {
            return $this->errors;
        }

        private function parseRules($rules)
        {
            if (is_string($rules)) {
                $rules = explode('|', $rules);
            }

            $parsedRules = [];

            foreach ($rules as $rule) {
                if (is_string($rule)) {
                    $parsedRules[] = $this->createRule($rule);
                } elseif ($rule instanceof Rule) {
                    $parsedRules[] = $rule;
                }
            }

            return $parsedRules;
        }

        private function createRule($rule)
        {
            $parts = explode(':', $rule, 2);
            $ruleName = $parts[0];
            $parameters = isset($parts[1]) ? explode(',', $parts[1]) : [];

            $ruleClass = 'App\\Core\\Validation\\Rules\\' . ucfirst($ruleName);

            if (!class_exists($ruleClass)) {
                throw new \Exception("Validation rule {$ruleName} does not exist.");
            }

            return new $ruleClass(...$parameters);
        }

        private function getValue($attribute)
        {
            return $this->data[$attribute] ?? null;
        }

        private function addError($attribute, $message)
        {
            if (!isset($this->errors[$attribute])) {
                $this->errors[$attribute] = [];
            }

            $this->errors[$attribute][] = $message;
        }
    }