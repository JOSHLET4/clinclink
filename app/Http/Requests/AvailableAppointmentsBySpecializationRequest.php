<?php

namespace App\Http\Requests;

use App\Rules\StartBeforeEndTimestampRule;
use Illuminate\Foundation\Http\FormRequest;

class AvailableAppointmentsBySpecializationRequest extends FormRequest
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
            'specialization_id' => 'required|min:1|integer',
            'start_times_tamp' => [
                'required',
                'min:1',
                'date_format:Y-m-d H:i:s',
                new StartBeforeEndTimestampRule(
                    $this->start_times_tamp,
                    $this->end_times_tamp
                )
            ],
            'end_times_tamp' => 'required|min:1|date_format:Y-m-d H:i:s',
        ];
    }
}
