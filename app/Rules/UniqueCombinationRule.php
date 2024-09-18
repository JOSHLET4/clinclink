<?php

namespace App\Rules;

use Closure;
use DB;
use Illuminate\Contracts\Validation\ValidationRule;

class UniqueCombinationRule implements ValidationRule
{
    public $table;
    public $id;
    public $request;
    
    public function __construct($table, $id,  $request)
    {
        $this->table = $table;
        $this->id = $id;
        $this->request = $request;
    }
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // $omitFields = ['_method', '_token'];
        $arrayStrings = array();
        $query = DB::table($this->table);
        foreach ($this->request as $attribute => $value) {
            // if (in_array($attribute, $omitFields)) continue;
            $query->where($attribute, $value);
            $arrayStrings[$attribute] = $attribute . '=' . $value;
        }
        if ($this->id) $query->where('id', '<>', $this->id);
        $exists = $query->exists();
        if ($exists)
            $fail('La combinacion de atributos ' . implode(', ', $arrayStrings) . ' no esta permitida');
    }
}
