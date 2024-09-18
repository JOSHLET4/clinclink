<?php

namespace App\Rules;

use Closure;
use DB;
use Illuminate\Contracts\Validation\ValidationRule;

class AppointmentRule implements ValidationRule
{
    public $table;
    public $id;
    public $roomId;
    public $startTimestamp;
    public $endTimestamp;
    
    public function __construct($id, $roomId, $startTimestamp,  $endTimestamp)
    {
        $this->table = 'appointments';
        $this->id = $id;
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
        // Lógica de validación para verificar conflicto de horarios
        $startTimestamp = $this->startTimestamp;
        $endTimestamp = $this->endTimestamp;

        // Verifica si ya existe una cita en el mismo rango de fechas/horas
        $query = DB::table($this->table)
            ->where('room_id', $this->roomId)
            ->where(function ($query) use ($startTimestamp, $endTimestamp) {
                $query->where('start_timestamp', '<', $endTimestamp)
                 ->where('end_timestamp', '>', $startTimestamp);
        });
        if ($this->id) $query->where('id', '<>', $this->id);
        $exists = $query->exists();
        if ($exists) {
            $fail('La combinacion de fechas ya esta registrada');
        }
    }
}
