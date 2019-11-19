<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class Chinese implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return preg_match("/^[\x{4e00}-\x{9fa5}]+$/u", $value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute is not chinese';
    }
}
