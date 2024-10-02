<?php
namespace App\Services;
use App\Models\Appointment;
use App\Models\DoctorDetail;
use App\Models\Schedule;
use DateTime;
use DB;

class AppointmentService
{
  public function __construct()
  {
  }

  // * tiempos disponibles del doctor y el cuarto combinadas
  public function getAvailableAppointmentsByDoctorAndRoom(
    $inputDoctorId,
    $specializationId,
    $inputStartTimestamp,
    $inputEndTimestamp
  ): array {
    $roomService = new RoomService();
    $availableHourlyTimeRanges = [];

    $availableAppointmentsByDoctorAttributes = $this
      ->getAvailableTimesByDoctorAttributes(
        $inputDoctorId,
        $specializationId,
        $inputStartTimestamp,
        $inputEndTimestamp
      );

    $availableRoomsByDateRange = $roomService
      ->getAvailableRoomsByDateRange(
        null,
        $inputStartTimestamp,
        $inputEndTimestamp
      );

    // Procesar rangos de tiempos disponibles de doctores
    foreach ($availableAppointmentsByDoctorAttributes as $doctorAppointment) {
      $this->processAvailableEntitieTimes(
        $availableHourlyTimeRanges,
        $doctorAppointment['available_hours'],
        $doctorAppointment['doctor_id'],
        'doctor'
      );

    }
    // Procesar rangos de tiempos disponibles de cuartas
    foreach ($availableRoomsByDateRange as $room) {
      $this->processAvailableEntitieTimes(
        $availableHourlyTimeRanges,
        $room['available_hours'],
        $room['room_id'],
        'room'
      );

    }

    // Filtrar el array para remover entradas vacías de doctors_id o rooms_id
    $availableHourlyTimeRanges = array_filter($availableHourlyTimeRanges, function ($entry) {
      return !empty($entry['doctors_id']) && !empty($entry['rooms_id']);
    });

    // Ordenar el array por start_timestamp
    usort($availableHourlyTimeRanges, function ($a, $b) {
      return strcmp($a['start_timestamp'], $b['start_timestamp']);
    });

    return $availableHourlyTimeRanges;
  }

  // * procesar rangos de fechas disponibles por entidad
  private function processAvailableEntitieTimes(
    &$availableHourlyTimeRanges,
    $availableHours,
    $entityId,
    $type
  ) {
    // recorre todos los rangos de tiempos encontrados
    foreach ($availableHours as $availableTimesRange) {
      $currentTime = new DateTime($availableTimesRange['from_time']);
      $endTime = new DateTime($availableTimesRange['to_time']);
      $lastEnd = (clone $currentTime)->modify('+1 hour');

      // guardar fecha + 1hora mientras lastEntd < a la hora final
      while ($lastEnd < $endTime) {
        $this->addIdToAvailableHourlyTimesRanges(
          $availableHourlyTimeRanges,
          $currentTime->format('Y-m-d H:i:s'),
          $lastEnd->format('Y-m-d H:i:s'),
          $entityId,
          $type
        );
        $currentTime = $lastEnd;
        $lastEnd = (clone $currentTime)->modify('+1 hour');
      }

      // Añadir el último rango horario
      $this->addIdToAvailableHourlyTimesRanges(
        $availableHourlyTimeRanges,
        $currentTime->format('Y-m-d H:i:s'),
        $endTime->format('Y-m-d H:i:s'),
        $entityId,
        $type
      );
    }
  }

  // * guardar el id de doctor o de cuarto de rango de fechas disponibles
  function addIdToAvailableHourlyTimesRanges(
    &$schedule,
    $start,
    $end,
    $newId,
    $type
  ) {
    // Variable para rastrear si se encontró el rango
    $found = false;
    foreach ($schedule as &$entry) {
      if ($entry['start_timestamp'] === $start && $entry['end_timestamp'] === $end) {
        $found = true; // Marcamos que encontramos el rango
        if ($type === 'doctor') {
          if (!in_array($newId, $entry['doctors_id'])) {
            $entry['doctors_id'][] = $newId;
          }
        } elseif ($type === 'room') {
          if (!in_array($newId, $entry['rooms_id'])) {
            $entry['rooms_id'][] = $newId;
          }
        }
        break;
      }
    }
    // Si no se encontró el rango, se agrega un nuevo elemento
    if (!$found) {
      $schedule[] = [
        'start_timestamp' => $start,
        'end_timestamp' => $end,
        'doctors_id' => ($type === 'doctor') ? [$newId] : [],
        'rooms_id' => ($type === 'room') ? [$newId] : [],
      ];
    }
  }

  // * tiempos libres del medico entre su horario de trabajo
  public function getAvailableTimesByDoctorAttributes(
    $inputDoctorId,
    $specializationId,
    $inputStartTimestamp,
    $inputEndTimestamp
  ): array {

    // obtener las citas por especializdad y horas de trabajo del medico
    $appointments = $this->getFilterAppointmentsByDoctorAttributes(
      $inputDoctorId,
      $specializationId,
      $inputStartTimestamp,
      $inputEndTimestamp,
    );

    /*
        ids de los doctores registrados (si inputDoctorId no esta vacio
        envia ese id especifico
    */
    $doctorsId = $inputDoctorId
      ? [$inputDoctorId]
      : DoctorDetail::distinct()->pluck('doctor_id');

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
    return $availableHours;
  }

  // * todas las citas entre el rango de fechas con filtro de atributos de doctor (opcionales)
  public function getFilterAppointmentsByDoctorAttributes(
    $doctorId = null,
    $specialization_id = null,
    $startTimestamp,
    $endTimestamp
  ) {
    /* 
        citas del medico x por especializacion x que se encuentran entre la 
        hora de trabajo de inicio y final del medico de las fechas de inicio 
        x y fecha final 'ingresadas'
    */
    return Appointment::select(
      'users.id as doctor_id',
      'appointments.id as appointment_id',
      'appointments.room_id as room_id',
      'specializations.id as specialization_id',
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
      ->where('appointment_status_id', '<>', 2)
      ->groupBy(
        'users.id',
        'appointments.id',
        'appointments.room_id',
        'specializations.id',
        'schedules.time_start',
        'schedules.time_end',
        'appointments.start_timestamp',
        'appointments.end_timestamp',
        'appointments.appointment_status_id'
      )
      ->get();
  }

  // * obtener slots de timepos disponibles segun rango de fechas y horario del medico
  function getFreeSlots(
    $doctorId,
    $startDate,
    $endDate,
    $appointments
  ): array {
    /*
        - ya no es importante guardar hora inicio y hora final de las fechas ingresadas
            por que se crea un nuevo limite entre la currentDate y las horas inicio y fin
            de la hora de trabajo del medico.

        - la hora inicio y final ingresadas ya fueron evaluadas en la consulta query 
            para obtener solo las citas que se encuentran en el rango de trabajo del medico
    */

    $freeSlots = [];
    $currentDate = new DateTime($startDate);
    $startDate = new DateTime($startDate);
    $endDate = new DateTime($endDate);

    while ($currentDate <= $endDate) {
      // Rango de trabajo del día actual

      // ! posible eliminacion (actualizar dias de trabajo segun dia)
      // obtener el numero del dia de la fecha actual
      $currentDayOfWeek = $currentDate->format('N');
      // botener horario del doctor segun el dia especifico
      $doctorScheduleDay = Schedule::select('time_start', 'time_end')
        ->where('day_of_week', $currentDayOfWeek)
        ->where('doctor_id', $doctorId)->first();

      // si no encuentra horario ese dia, se salta el dia
      if (
        !$doctorScheduleDay ||
        !$doctorScheduleDay->time_start ||
        !$doctorScheduleDay->time_end
      ) {
        $currentDate->modify('+1 day');
        continue;
      }

      // fecha actual " " hora inicio medico
      $dayStart = new DateTime($currentDate->format('Y-m-d') . " " .
        $doctorScheduleDay->time_start);
      // fecha actual " " hora fin medico
      $dayEnd = new DateTime($currentDate->format('Y-m-d') . " " .
        $doctorScheduleDay->time_end);

      // Ajustar dayStart si es el primer día y $startDate es mayor al horario del doctor
      if ($currentDate->format('Y-m-d') === $startDate->format('Y-m-d')) {
        if ($startDate > $dayStart) {
          $dayStart = $startDate;
        }
      }

      // Ajustar dayEnd si es el último día y $endDate es menor al horario del doctor
      if ($currentDate->format('Y-m-d') === $endDate->format('Y-m-d')) {
        if ($endDate < $dayEnd) {
          $dayEnd = $endDate;
        }
      }

      // Obtener las citas del doctor para este día
      $dayAppointments = array_filter(
        $appointments,
        function ($appointment) use ($currentDate) {
          return (
            new DateTime($appointment['appointment_start_timestamp']))
            ->format('Y-m-d') === $currentDate->format('Y-m-d');
        }
      );

      // Si no hay citas, el día completo está libre
      if (empty($dayAppointments)) {
        $freeSlots[] = [$dayStart, $dayEnd]; // guarda fecha actual " " hora de inicio medico y final medico
      } else {
        // Ordenar las citas por hora de inicio (orden ascendente)
        usort($dayAppointments, function ($a, $b) {
          return strtotime($a['appointment_start_timestamp']) -
            strtotime($b['appointment_start_timestamp']);
        });

        // Inicializar el inicio de la primera ventana libre
        $lastEnd = $dayStart;

        foreach ($dayAppointments as $appointment) {
          $appointmentStart = new DateTime(
            $appointment['appointment_start_timestamp']
          ); // fecha y hora inicio de cita
          $appointmentEnd = new DateTime(
            $appointment['appointment_end_timestamp']
          ); // fecha y hora final de cita

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