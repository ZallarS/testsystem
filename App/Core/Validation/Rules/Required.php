<?php

    namespace App\Core\Validation\Rules;

    use App\Core\Validation\Rule;

    class Required extends Rule
    {
        public function passes($attribute, $value)
        {
            return !empty($value) || $value === '0';
        }

        public function message($attribute)
        {
            return "The {$this->getAttributeName($attribute)} field is required.";
        }
    }