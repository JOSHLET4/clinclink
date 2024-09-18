<?php

namespace App\Http\Requests;

use App\Rules\UniqueCombinationRule;
use Illuminate\Foundation\Http\FormRequest;

class DoctorDetailSpecializationRequest extends FormRequest
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
            'doctor_detail_id' => [
                'required',
                'min:1',
                'integer',
                new UniqueCombinationRule(
                    'doctor_detail_specializations',
                    $this->route('{doctor_detail_specialization'),
                    [
                        'doctor_detail_id' => $this->doctor_detail_id,
                        'specialization_id' => $this->specialization_id,
                    ]
                )
            ],
            'specialization_id' => 'required|min:1|integer'
        ];
    }
}
