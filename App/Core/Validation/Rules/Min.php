<?php

    namespace App\Core\Validation\Rules;

    use App\Core\Validation\Rule;

    class Min extends Rule
    {
        protected $min;

        public function __construct($min)
        {
            $this->min = $min;
        }

        public function passes($attribute, $value)
        {
            return strlen($value) >= $this->min;
        }

        public function message($attribute)
        {
            return "The {$this->getAttributeName($attribute)} must be at least {$this->min} characters.";
        }
    }