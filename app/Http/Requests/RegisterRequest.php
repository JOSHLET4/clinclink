<?php

namespace App\Http\Requests;

use App\Rules\UniqueOrSoftDeletedRule;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
        return [
            'username' => [
                'required',
                'min:3',
                new UniqueOrSoftDeletedRule,
            ],
            'first_name' => 'required|min:3',
            'last_name' => 'required|min:3',
            'email' => [
                'required',
                'email',
                new UniqueOrSoftDeletedRule,
            ],
            'password' => 'required|min:5|confirmed',
            'phone_number' => 'required|min:8',
            'date_of_birth' => 'required|date',
            'file' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ];
    }
}

