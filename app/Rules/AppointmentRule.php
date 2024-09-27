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

        // Verifica si ya existe una cita en el mismo rango de fechas/horas
        $query = DB::table($this->table)
            ->where('room_id', $this->roomId)
            ->where('appointment_status_id', '<>', 2)
            ->where(function ($query) use ($startTimestamp, $endTimestamp) {
                $query->where('start_timestamp', '<', $endTimestamp)
                    ->where('end_timestamp', '>', $startTimestamp);
            });
        // para actualizacones
        if ($this->id)
            $query->where('id', '<>', $this->id);
        if ($query->exists())
            $fail('La combinacion de fechas ya esta registrada');

    }
}
