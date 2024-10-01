<?php

namespace App\Rules;

use App\Models\Schedule;
use App\Models\User;
use Closure;
use DateTime;
use DB;
use Illuminate\Contracts\Validation\ValidationRule;

class AppointmentRule implements ValidationRule
{
    public $table;
    public $id;
    public $doctorId;
    public $roomId;
    public $startTimestamp;
    public $endTimestamp;

    public function __construct($id, $doctorId, $roomId, $startTimestamp, $endTimestamp)
    {
        $this->table = 'appointments';
        $this->id = $id;
        $this->doctorId = $doctorId;
        $this->roomId = $roomId;
        $this->startTimestamp = $startTimestamp;
        $this->endTimestamp = $endTimestamp;
    }
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {

        $startTimestamp = $this->startTimestamp;
        $endTimestamp = $this->endTimestamp;

        // ? solo aplicara esta validacion se le manda un doctorId y no es null
        if ($this->doctorId) {
            $currentDay = (new DateTime($startTimestamp))->format('N');
            $schedule = Schedule::select('*')
                ->where('doctor_id', $this->doctorId)
                ->where('day_of_week', $currentDay)
                ->first();

            if ($schedule) {
                $appointmentStartTime = (new DateTime($startTimestamp))->format('H:i:s');
                $appointmentEndTime = (new DateTime($endTimestamp))->format('H:i:s');
                $doctorScheduleStart = (new DateTime($schedule->time_start))->format('H:i:s');
                $doctorScheduleEnd = (new DateTime($schedule->time_end))->format('H:i:s');
                if (
                    $doctorScheduleStart > $appointmentStartTime ||
                    $doctorScheduleEnd < $appointmentEndTime
                ) {
                    $fail('Las horas no son compatibles con el horario del doctor');
                }
            } else {
                $fail('Ese dia el doctor no trabaja');
            }
        }

        $id = $this->id;

        // consulta todos las citas entre el rango de fechas
        $query = DB::table($this->table)
            ->where('appointment_status_id', '<>', 2)
            ->where(function ($query) use ($startTimestamp, $endTimestamp) {
                $query->where('start_timestamp', '<', $endTimestamp)
                    ->where('end_timestamp', '>', $startTimestamp);
            })
            ->when($id, function ($query) use ($id) {
                return $query->where('id', '<>', $id); // excepcion para actualizacion
            })
        ;

        // Verificación de cuarto ocupado
        $roomQuery = clone $query;
        if ($roomQuery->where('room_id', $this->roomId)->exists()) {
            $fail('El cuarto ya está ocupado en ese rango de fechas');
        }

        // Verificación de médico ocupado
        $doctorQuery = clone $query;
        if ($doctorQuery->where('doctor_id', $this->doctorId)->exists()) {
            $fail('El médico ya está ocupado en ese horario');
        }
    }
}
