<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = $this->route('user');
        return [
            'role_id' => 'required',
            'username' => [
                'required',
                'min:3',
                Rule::unique('users')->ignore($userId),
            ],
            'first_name' => 'required|min:3',
            'last_name' => 'required|min:3',
            'email' => [
                'required',
                'email',
                // Verificar que el email sea único incluso entre registros eliminados
                Rule::unique('users')->ignore($userId),
            ],
            'password' => 'required|min:5|confirmed',
            'phone_number' => 'required|min:8',
            'date_of_birth' => 'required|date',
            'file' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ];
    }
}
