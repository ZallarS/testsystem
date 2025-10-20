<?php

    namespace App\Core\Validation;

    abstract class Rule
    {
        abstract public function passes($attribute, $value);

        abstract public function message($attribute);

        protected function getAttributeName($attribute)
        {
            $replacements = [
                '_' => ' ',
                '.' => ' '
            ];

            return str_replace(array_keys($replacements), array_values($replacements), $attribute);
        }
    }