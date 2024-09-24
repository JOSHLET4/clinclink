<?php

namespace App\Http\Controllers;

use App\Http\Requests\AppointmentRequest;
use App\Http\Requests\AvailableAppointmentsBySpecializationRequest;
use App\Models\Appointment;
use App\Models\Schedule;
use App\Models\User;
use App\Utils\SimpleCRUD;
use App\Utils\SimpleJSONResponse;
use DateTime;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public $crud;

    public function __construct()
    {
        $this->crud = new SimpleCRUD(new Appointment);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        return $this->crud->index(null, $request->pagination);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AppointmentRequest $request): JsonResponse
    {
        return $this->crud->store($request);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        return $this->crud->show($id);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(AppointmentRequest $request, string $id): JsonResponse
    {
        return $this->crud->update($request, $id);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        return $this->crud->destroy($id);
    }

    public function cancelAppointment($id): JsonResponse
    {

    }

    public function appointmentsBySpecialization(AvailableAppointmentsBySpecializationRequest $request): JsonResponse
    {
        return SimpleJSONResponse::successResponse(
            $this->doctorAppointmentsBySpecialization(
                $request->input('doctor_id'),
                $request->input('specialization_id'),
                $request->input('start_times_tamp'),
                $request->input('end_times_tamp')
            ),
            'Registros consultados exitosamente',
            200
        );
    }

    public function availableAppointmentsBySpecialization(AvailableAppointmentsBySpecializationRequest $request): JsonResponse
    {
        // valores de entrada
        $inputDoctorId = $request->input('doctor_id');
        $especializationId = $request->input('specialization_id');
        $inputStartTimestamp = $request->input('start_times_tamp');
        $inputEndTimestamp = $request->input('end_times_tamp');

        // obtengo las citas por especializdad y horas de trabajo del medico
        $appointments = $this->doctorAppointmentsBySpecialization(
            $inputDoctorId,
            $especializationId,
            $inputStartTimestamp,
            $inputEndTimestamp,
        );

        // ids de los doctores registrados
        $doctorsId = $appointments->pluck('doctor_id')->unique();

        // horas disponibles para todos los medicos
        $availableHours = [];

        foreach ($doctorsId as $id) {
            $doctorAppointments = $appointments->where('doctor_id', $id);

            $freeSlots = $this->getFreeSlots(
                $id,
                $inputStartTimestamp,
                $inputEndTimestamp,
                $doctorAppointments->toArray()
            );

            // formato a los slots de horas disponbiles
            $formattedDates = array_map(function ($slot) {
                return [
                    'from_time' => $slot[0]->format('Y-m-d H:i:s'),
                    'to_time' => $slot[1]->format('Y-m-d H:i:s')
                ];
            }, $freeSlots);

            $availableHours[] = [
                'doctor_id' => $id,
                'available_hours' => $formattedDates
            ];
        }
        return SimpleJSONResponse::successResponse(
            $availableHours,
            'Registros consultados exitosamente',
            200
        );
    }

    public function doctorAppointmentsBySpecialization($doctorId = null, $specialization_id, $startTimestamp, $endTimestamp)
    {
        /* 
            citas del medico x por especializacion x que se encuentran entre la 
            hora de trabajo de inicio y final del medico de las fechas de inicio 
            x y fecha final 'ingresadas'
        */
        return Appointment::select(
            'users.id as doctor_id',
            'appointments.id as appointment_id',
            'schedules.time_start as doctor_time_start',
            'schedules.time_end as doctor_time_end',
            'appointments.start_timestamp as appointment_start_timestamp',
            'appointments.end_timestamp as appointment_end_timestamp',
            'appointments.appointment_status_id'
        )
            ->join('users', 'appointments.doctor_id', '=', 'users.id')
            ->join('schedules', 'schedules.doctor_id', '=', 'users.id')
            ->join('doctor_details', 'doctor_details.doctor_id', '=', 'users.id')
            ->join('doctor_detail_specializations', 'doctor_detail_specializations.doctor_detail_id', '=', 'doctor_details.id')
            ->join('specializations', 'doctor_detail_specializations.specialization_id', '=', 'specializations.id')
            ->where('specializations.id', $specialization_id)
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
                'schedules.time_start',
                'schedules.time_end',
                'appointments.start_timestamp',
                'appointments.end_timestamp'
            )
            ->get();
    }

    // solo toma el primer horario de el medico, pero deberia de cambiar. 

    // function getFreeSlots($doctorId, $startDate, $endDate, $workStart, $workEnd, $appointments): array
    function getFreeSlots($doctorId, $startDate, $endDate, $appointments): array
    {
        /*
            - ya no es importante guardar hora inicio y hora final de las fechas ingresadas
                por que se crea un nuevo limite entre la currentDate y las horas inicio y fin
                de la hora de trabajo del medico.

            - la hora inicio y final ingresadas ya fueron evaluadas en la consulta query 
                para obtener solo las citas que se encuentran en el rango de trabajo del medico
        */

        $freeSlots = [];
        $currentDate = new DateTime($startDate);
        $endDate = new DateTime($endDate);

        while ($currentDate <= $endDate) {
            // Rango de trabajo del día actual

            // ! posible eliminacion (actualizar dias de trabajo segun dia)
            // obtener el numero del dia de la fecha actual
            $currentDayOfWeek = $currentDate->format('N');
            // botener horario del doctor segun el dia especifico
            $doctorScheduleDay =  Schedule::select('time_start', 'time_end')
                        ->where('day_of_week', $currentDayOfWeek)
                        ->where('doctor_id', $doctorId)->first(); 

            // si no encuentra horario ese dia, se salta el dia
            if (!$doctorScheduleDay || !$doctorScheduleDay->time_start || !$doctorScheduleDay->time_end) {
                $currentDate->modify('+1 day');
                continue;
            }
           
            // fecha actual " " hora inicio medico
            $dayStart = new DateTime($currentDate->format('Y-m-d') . " " . $doctorScheduleDay->time_start);
            // fecha actual " " hora fin meidoc
            $dayEnd = new DateTime($currentDate->format('Y-m-d') . " " . $doctorScheduleDay->time_end);

            // Obtener las citas del doctor para este día
            $dayAppointments = array_filter($appointments, function ($appointment) use ($currentDate) {
                return (
                    new DateTime($appointment['appointment_start_timestamp']))->format('Y-m-d') === $currentDate->format('Y-m-d');
            });

            // Si no hay citas, el día completo está libre
            if (empty($dayAppointments)) {
                $freeSlots[] = [$dayStart, $dayEnd]; // guarda fecha actual " " hora de inicio medico y final medico
            } else {
                // Ordenar las citas por hora de inicio (orden ascendente)
                usort($dayAppointments, function ($a, $b) {
                    return strtotime($a['appointment_start_timestamp']) - strtotime($b['appointment_start_timestamp']);
                });

                // Inicializar el inicio de la primera ventana libre
                $lastEnd = $dayStart;

                foreach ($dayAppointments as $appointment) {

                    // ! posible eliminacion (saltar cita si esta cancelada)
                    // ? 2 representa el estado cancelado (sub consulta para consultar ids para saltar)
                    if ($appointment['appointment_status_id'] == 2) {
                        continue;
                    }

                    $appointmentStart = new DateTime($appointment['appointment_start_timestamp']); // fecha y hora inicio de cita
                    $appointmentEnd = new DateTime($appointment['appointment_end_timestamp']); // fecha y hora final de cita

                    // Si hay un hueco entre el final de la última cita y el inicio de la siguiente

                    // if (fecha 10:00:00 < fecha 11:00:00) hueco entre esas horas
                    if ($lastEnd < $appointmentStart) {
                        $freeSlots[] = [$lastEnd, $appointmentStart];
                    }

                    // Actualizar el final de la última cita fecha 11:00:00
                    $lastEnd = $appointmentEnd;
                }

                // Si hay espacio después de la última cita
                if ($lastEnd < $dayEnd) {
                    $freeSlots[] = [$lastEnd, $dayEnd];
                }
            }
            // Avanzar al siguiente día
            $currentDate->modify('+1 day');
        }

        return $freeSlots;
    }


}
