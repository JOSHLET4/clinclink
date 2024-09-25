<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function availableRoomsByRoomId(Request $request, $roomId)
    {
        return $roomId;
    }

    public function filterAppointmentsRoomId($doctorId = null, $specialization_id = null, $startTimestamp, $endTimestamp)
    {
        /* 
            citas del medico x por especializacion x que se encuentran entre la 
            hora de trabajo de inicio y final del medico de las fechas de inicio 
            x y fecha final 'ingresadas'
        */
        return Appointment::select(
            'users.id as doctor_id',
            'appointments.id as appointment_id',
            'appointments.room_id as room_id',
            'appointments.start_timestamp as appointment_start_timestamp',
            'appointments.end_timestamp as appointment_end_timestamp',
            'appointments.appointment_status_id'
        )
            ->join('users', 'appointments.doctor_id', '=', 'users.id')
            ->join('schedules', 'schedules.doctor_id', '=', 'users.id')
            ->join('doctor_details', 'doctor_details.doctor_id', '=', 'users.id')
            ->join('doctor_detail_specializations', 'doctor_detail_specializations.doctor_detail_id', '=', 'doctor_details.id')
            ->join('specializations', 'doctor_detail_specializations.specialization_id', '=', 'specializations.id')
            // ->where('specializations.id', $specialization_id)
            ->when($specialization_id, function ($query) use ($specialization_id) {
                return $query->where('specializations.id', $specialization_id);
            })
            ->where('appointments.start_timestamp', '<', $endTimestamp) // 2024-09-09 20:00:00
            ->where('appointments.end_timestamp', '>', $startTimestamp) // 2024-09-05 08:00:00
            // cuenta con citas a partir de la hora de inicio puntual 8:00:00
            ->where('schedules.time_start', '<=', DB::raw('TIME(appointments.start_timestamp)'))
            // No lee registros que empiezan a al hora de finalizacion (16:00:00 x mal) 
            ->where('schedules.time_end', '>', DB::raw('TIME(appointments.start_timestamp)'))
            ->when($doctorId, function ($query) use ($doctorId) {
                return $query->where('users.id', $doctorId);
            })
            ->groupBy(
                'users.id',
                'appointments.id',
                'appointments.room_id',
                'appointments.start_timestamp',
                'appointments.end_timestamp'
            )
            ->get();
    }
}
