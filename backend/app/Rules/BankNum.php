<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class BankNum implements Rule
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
        return preg_match("/^(\d{16}|\d{19}|\d{17})$/", $value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute is not bank number format';
    }
}
