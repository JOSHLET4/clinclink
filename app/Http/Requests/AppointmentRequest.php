<?php

namespace App\Http\Requests;

use App\Models\Appointment;
use App\Rules\AppointmentRule;
use DB;
use Illuminate\Foundation\Http\FormRequest;

class AppointmentRequest extends FormRequest
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
            'room_id' => 'required|min:1|integer',
            'appointment_status_id' => 'required|min:1|integer',
            'patient_id' => 'required|min:1|integer',
            'doctor_id' => 'required|min:1|integer',
            'start_timestamp' => [
                'required',
                'min:1',
                'date_format:Y-m-d H:i:s',
                // validar que la fecha y hora ingresada no esten registradas
                new AppointmentRule(
                    $this->route('appointment'),
                    $this->room_id,
                    $this->start_timestamp,
                    $this->end_timestamp,
                )
            ],
            'end_timestamp' => 'required|min:1|date_format:Y-m-d H:i:s',
        ];
    }
}
