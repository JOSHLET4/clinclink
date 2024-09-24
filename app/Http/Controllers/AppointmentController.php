<?php

namespace App\Http\Controllers;

use App\Http\Requests\AppointmentRequest;
use App\Models\Appointment;
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

    public function availableDatesBySpecialty()
    {

        // // Ejemplo de uso
        // $appointments = [
        //     ['appointment_start_timestamp' => '2024-05-03 09:00:00', 'appointment_end_timestamp' => '2024-05-03 10:00:00'],
        //     ['appointment_start_timestamp' => '2024-05-03 10:00:00', 'appointment_end_timestamp' => '2024-05-03 11:00:00'],
        //     ['appointment_start_timestamp' => '2024-05-04 13:00:00', 'appointment_end_timestamp' => '2024-05-04 14:00:00']
        // ];

        // $freeSlots = $this->getFreeSlots(1, '2024-05-03 08:00:00', '2024-05-10 16:00:00', '07:00:00', '19:00:00', $appointments);

        // // Formatear y mostrar los resultados
        // foreach ($freeSlots as $slot) {
        //     echo "Libre desde: " . $slot[0]->format('Y-m-d H:i:s') . " hasta " . $slot[1]->format('Y-m-d H:i:s') . "\n";
        // }



        // obtengo las citas por especializdad y horas de trabajo del medico
        $appointments = $this->doctorAppointmentsBySpecialization();
        // ids de los doctores registrados
        $doctorsId = $appointments->pluck('doctor_id')->unique();

        foreach ($doctorsId as $id) {
            $doctorAppointments = $appointments->where('doctor_id', $id);
            $doctorTimeStart = $doctorAppointments->pluck('doctor_time_start')->unique()[0];
            $doctorTimeEnd = $doctorAppointments->pluck('doctor_time_end')->unique()[0];

            // faltan los valores de entrada fechas
            $freeSlots = $this->getFreeSlots($id, '2024-09-05 08:00:00', '2024-09-07 20:00:00', $doctorTimeStart, $doctorTimeEnd, $doctorAppointments->toArray());
            // Formatear y mostrar los resultados
            foreach ($freeSlots as $slot) {
                echo "Libre desde: " . $slot[0]->format('Y-m-d H:i:s') . " hasta " . $slot[1]->format('Y-m-d H:i:s') . "\n";
            }
        }

        // $appointments = [
        //     ['appointment_start_timestamp' => '2024-05-03 09:00:00', 'appointment_end_timestamp' => '2024-05-03 10:00:00'],
        //     ['appointment_start_timestamp' => '2024-05-03 10:00:00', 'appointment_end_timestamp' => '2024-05-03 11:00:00'],
        //     ['appointment_start_timestamp' => '2024-05-04 13:00:00', 'appointment_end_timestamp' => '2024-05-04 14:00:00']
        // ];



        // return SimpleJSONResponse::successResponse($freeSlots, 'consulta correcta', 200);
    }

    public function doctorAppointmentsBySpecialization()
    {
        /*
            citas del medico x por especializacion x que se encuentran entre la 
            hora de trabajo de inicio y final del medico de las fechas de inicio 
            x y fecha final 'ingresadas'
        */

        // faltan los valores de entrada hora y fecha de ingreso
        return Appointment::select(
            'users.id as doctor_id',
            'appointments.id as appointment_id',
            'schedules.time_start as doctor_time_start',
            'schedules.time_end as doctor_time_end',
            'appointments.start_timestamp as appointment_start_timestamp',
            'appointments.end_timestamp as appointment_end_timestamp'
        )
            ->join('users', 'appointments.doctor_id', '=', 'users.id')
            ->join('schedules', 'schedules.doctor_id', '=', 'users.id')
            ->join('doctor_details', 'doctor_details.doctor_id', '=', 'users.id')
            ->join('doctor_detail_specializations', 'doctor_detail_specializations.doctor_detail_id', '=', 'doctor_details.id')
            ->join('specializations', 'doctor_detail_specializations.specialization_id', '=', 'specializations.id')
            ->where('specializations.name', 'dermatologia')
            ->where('schedules.time_start', '>=', '08:00:00')
            ->where('schedules.time_end', '<=', '20:00:00')
            ->where('appointments.start_timestamp', '<', '2024-09-07 20:00:00')
            ->where('appointments.end_timestamp', '>', '2024-09-05 08:00:00')
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

    function getFreeSlots($doctorId, $startDate, $endDate, $workStart, $workEnd, $appointments)
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

            // fecha actual " " hora inicio medico
            $dayStart = new DateTime($currentDate->format('Y-m-d') . " " . $workStart);
            // fecha actual " " hora fin meidoc
            $dayEnd = new DateTime($currentDate->format('Y-m-d') . " " . $workEnd);

            // Obtener las citas del doctor para este día
            $dayAppointments = array_filter($appointments, function ($appointment) use ($currentDate) {
                return (new DateTime($appointment['appointment_start_timestamp']))->format('Y-m-d') === $currentDate->format('Y-m-d');
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
                    $appointmentStart = new DateTime($appointment['appointment_start_timestamp']); // fecha y hora inicio de cita
                    $appointmentEnd = new DateTime($appointment['appointment_end_timestamp']); // fecha y hora final de cita

                    // Si hay un hueco entre el final de la última cita y el inicio de la siguiente

                    // if (fecha 10:00:00 < fecha 11:00:00) hueco entre esas horas
                    if ($lastEnd < $appointmentStart) {
                        $freeSlots[] = [$lastEnd, $appointmentStart];
                    }
                    
                    // Actualizar el final de la última cita fecha 11:00:00
                    $lastEnd = $appointmentEnd;
                    
                    
                    // if ($lastEnd->format('H-i-s') > $workEnd) {
                    //     $lastEnd = new DateTime($lastEnd->format('Y-m-d') . " " . $workEnd);
                    // } 
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
