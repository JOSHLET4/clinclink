<?php

namespace App\Http\Requests;

use App\Models\Schedule;
use App\Rules\UniqueCombinationRule;
use Illuminate\Foundation\Http\FormRequest;

class ScheduleRequest extends FormRequest
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
                // validar unicidad de la duplicidad de doctor_id y day_of_week 
                new UniqueCombinationRule(
                    'schedules',
                    $this->route('schedule'),
                    [
                        'doctor_id' => $this->doctor_id,
                        'day_of_week' => $this->day_of_week
                    ]
                )
            ],
            // 'doctor_id' => 'required|min:1|integer',
            'day_of_week' => 'required|min:1|integer',
            'time_start' => 'required|min:1|date_format:H:i:s',
            'time_end' => 'required|min:1|date_format:H:i:s'
        ];
    }
}
