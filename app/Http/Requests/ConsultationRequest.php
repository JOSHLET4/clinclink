<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ConsultationRequest extends FormRequest
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
            'appointment_id' => [
                'required',
                'min:1',
                'integer',
                Rule::unique('consultations')->ignore($this->route('consultation')),
            ],
            'medical_record_id' => 'required|min:1|integer',
            'doctor_id' => 'required|min:1|integer',
            'diagnosis' => 'required|min:1',
            'treatment' => 'required|min:1',
            'notes' => 'required|min:1'
        ];
    }
}
