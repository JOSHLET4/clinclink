<?php

namespace App\Http\Requests;

use App\Rules\StartBeforeEndTimestampRule;
use Illuminate\Foundation\Http\FormRequest;

class AvailableTimesBySpecializationRequest extends FormRequest
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
            'doctor_id' => 'min:1|integer',
            'specialization_id' => 'min:1|integer',
            'start_timestamp' => [
                'required',
                'min:1',
                'date_format:Y-m-d H:i:s',
                new StartBeforeEndTimestampRule(
                    $this->start_timestamp,
                    $this->end_timestamp
                )
            ],
            'end_timestamp' => 'required|min:1|date_format:Y-m-d H:i:s',
        ];
    }
}
