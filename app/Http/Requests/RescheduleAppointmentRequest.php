<?php

namespace App\Http\Requests;

use App\Models\Appointment;
use App\Rules\AppointmentRule;
use App\Rules\StartBeforeEndTimestampRule;
use Illuminate\Foundation\Http\FormRequest;

class RescheduleAppointmentRequest extends FormRequest
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
        $doctor = Appointment::select('doctor_id')
            ->where('id', $this->route('appointment'))
            ->first();
        return [
            'room_id' => 'required|min:1|integer',
            'start_timestamp' => [
                'required',
                'min:1',
                'date_format:Y-m-d H:i:s',
                // validar que hora inicial no sea mayor a hora final
                new StartBeforeEndTimestampRule(
                    $this->start_timestamp,
                    $this->end_timestamp
                ),
                // validar que la fecha y hora ingresada no esten registradas
                new AppointmentRule(
                    $this->route('appointment'),
                    $doctor->doctor_id,
                    $this->room_id,
                    $this->start_timestamp,
                    $this->end_timestamp,
                )
            ],
            'end_timestamp' => 'required|min:1|date_format:Y-m-d H:i:s',
        ];
    }
}
