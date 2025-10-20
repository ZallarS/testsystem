<?php

    namespace App\Core\Validation\Rules;

    use App\Core\Validation\Rule;

    class Email extends Rule
    {
        public function passes($attribute, $value)
        {
            return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
        }

        public function message($attribute)
        {
            return "The {$this->getAttributeName($attribute)} must be a valid email address.";
        }
    }