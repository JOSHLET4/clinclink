<?php

namespace App\Rules;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UniqueOrSoftDeletedRule implements ValidationRule
{
    /**
     * Ejecuta la regla de validación.
     *
     * @param  string  $attribute  El nombre del campo siendo validado, por ejemplo 'username' o 'email'.
     * @param  mixed  $value  El valor del campo siendo validado.
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail  La función para registrar el error de validación.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Busca el usuario con el valor del campo, incluyendo los eliminados (SoftDeleted)
        $user = User::withTrashed()->where($attribute, $value)->first();

        // Si el usuario existe y está eliminado (SoftDeleted)
        if ($user && $user->trashed()) {
            $fail('El ' . $attribute . ' ya esta registrado y ha sido eliminado. Registro con id=' . $user->id . ' para reactivar');
            return;
        }

        // Si el usuario ya existe y no está eliminado
    }
}
