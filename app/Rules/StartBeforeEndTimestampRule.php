<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use DateTime;

class StartBeforeEndTimestampRule implements ValidationRule
{
    public $startTimestamp;
    public $endTimestamp;

    public function __construct($startTimestamp, $endTimestamp)
    {
        $this->startTimestamp = new DateTime($startTimestamp);
        $this->endTimestamp = new DateTime($endTimestamp);
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->endTimestamp < $this->startTimestamp)
            $fail('La fecha final es menor que la fecha inicial');
    }
}
