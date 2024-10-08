<?php
namespace App\Services;
use App\Models\Appointment;
use App\Models\Room;
use DateTime;

class RoomService
{
  public function __construct()
  {
  }

  // * tiempos libres de los cuartos en 24 horas
  public function getAvailableRoomsByDateRange(
    $inputRoomId,
    $inputStartTimestamp,
    $inputEndTimestamp
  ): array {

    // obtener las citas por rango de fechas y room_id (opcional)
    $appointments = $this->getFilterAppointmentsByDateRange(
      $inputRoomId,
      $inputStartTimestamp,
      $inputEndTimestamp
    );

    // ids de los cuartos registrados
    $roomsId = $inputRoomId
      ? [$inputRoomId]
      : Room::distinct()->pluck('id');

    // horas disponibles para todos los cuartos
    $availableHours = [];

    foreach ($roomsId as $id) {
      $roomAppointments = $appointments->where('room_id', $id);

      $freeSlots = $this->getFreeSlots(
        $inputStartTimestamp,
        $inputEndTimestamp,
        $roomAppointments->toArray()
      );

      // formato a los slots de horas disponbiles
      $formattedDates = array_map(function ($slot) {
        return [
          'from_time' => $slot[0]->format('Y-m-d H:i:s'),
          'to_time' => $slot[1]->format('Y-m-d H:i:s')
        ];
      }, $freeSlots);

      $availableHours[] = [
        'room_id' => $id,
        'available_hours' => $formattedDates
      ];
    }
    return $availableHours;
  }

  // * todas las citas del entre el rango de fechas con room_id (opcional)
  public function getFilterAppointmentsByDateRange(
    $roomId,
    $startTimestamp,
    $endTimestamp
  ) {
    return Appointment::select(
      'id',
      'room_id',
      'appointment_status_id',
      'start_timestamp as appointment_start_timestamp',
      'end_timestamp as appointment_end_timestamp',
    )
      ->when($roomId, function ($query) use ($roomId) {
        return $query->where('room_id', $roomId);
      })
      ->where('appointments.start_timestamp', '<', $endTimestamp)
      ->where('appointments.end_timestamp', '>', $startTimestamp)
      ->where('appointment_status_id', '<>', 2)
      ->groupBy(
        'id',
        'room_id',
        'appointment_status_id',
        'start_timestamp',
        'end_timestamp',
      )
      ->get();
  }

   // * obtener slots de timpos libres segun rango de fechas
  function getFreeSlots($startDate, $endDate, $appointments): array
  {
    $freeSlots = [];
    $currentDate = new DateTime($startDate);
    $endDate = new DateTime($endDate);

    while ($currentDate <= $endDate) {
      // fecha actual " " hora inicio dia
      $dayStart = new DateTime($currentDate->format('Y-m-d') . ' ' . '00:00:00');
      // fecha actual " " hora fin dia
      $dayEnd = new DateTime($currentDate->format('Y-m-d') . " " . '23:59:59');

      // Obtener citas de cuarto para el dia especifico
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
        $freeSlots[] = [$dayStart, $dayEnd];
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