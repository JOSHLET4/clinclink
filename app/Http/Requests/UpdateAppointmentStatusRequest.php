<?php

namespace App\Http\Requests;

use App\Models\Appointment;
use App\Rules\AppointmentRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAppointmentStatusRequest extends FormRequest
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
        $appointment = Appointment::where('id', $this->route('appointment'))->first();
        return [
            'appointment_status_id' => [
                'required',
                'min:1',
                'integer',
                new AppointmentRule(
                    $this->route('appointment'),
                    $appointment->doctor_id,
                    $appointment->room_id,
                    $appointment->start_timestamp,
                    $appointment->end_timestamp,
                )
            ] 
        ];
    }
}
