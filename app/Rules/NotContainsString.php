<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class NotContainsString implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $words = array('police', 'military', 'army');
        foreach ($words as $word) {
            // Fuzzy matches - case insensitive
            if (stripos($value, $word) !== false) {
                return false;
            }

        }
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return '":input" can not be reported. It may be disclose sensitive information.';
    }
}
