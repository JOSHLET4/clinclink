<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DoctorDetailRequest extends FormRequest
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
            'doctor_id' => [
                'required',
                'min:1',
                'integer',
                Rule::unique('doctor_details')->ignore($this->route('doctor_detail')),
            ],
            'license_number' => [
                'required',
                'min:1',
                'integer',
                Rule::unique('doctor_details')->ignore($this->route('doctor_detail')),
            ],
            'years_of_experience' => 'required|min:1|integer'
        ];
    }
}
