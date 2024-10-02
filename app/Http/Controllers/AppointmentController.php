<?php

namespace App\Http\Controllers;

use App\Http\Requests\AppointmentRequest;
use App\Http\Requests\AvailableTimesBySpecializationRequest;
use App\Http\Requests\RescheduleAppointmentRequest;
use App\Http\Requests\UpdateAppointmentStatusRequest;
use App\Models\Appointment;
use App\Services\AppointmentService;
use App\Utils\SimpleCRUD;
use App\Utils\SimpleJSONResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public $crud;
    public $appointmentService;

    public function __construct()
    {
        $this->crud = new SimpleCRUD(new Appointment);
        $this->appointmentService = new AppointmentService();
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

    public function updateAppointmentStatus(UpdateAppointmentStatusRequest $request, $id): JsonResponse
    {
        $data = Appointment::where('id', $id)->update([
            'appointment_status_id' => $request->input('appointment_status_id')
        ]);
        $data = Appointment::find($id);
        return SimpleJSONResponse::successResponse(
            $data,
            'Registro actualizado exitosamente',
            200
        );
    }

    public function rescheduleAppointment(RescheduleAppointmentRequest $request, $id)
    {
        $data = Appointment::where('id', $id)->update([
            'start_timestamp' => $request->input('start_timestamp'),
            'end_timestamp' => $request->input('end_timestamp')
        ]);
        $data = Appointment::find($id);
        return SimpleJSONResponse::successResponse(
            $data,
            'Registro actualizado exitosamente',
            200
        );
    }

    public function countAppointmentsByDateRange(AvailableTimesBySpecializationRequest $request)
    {
        return SimpleJSONResponse::successResponse(
            $this->appointmentService->
                getFilterAppointmentsByDoctorAttributes(
                    $request->input('doctor_id'),
                    $request->input('specialization_id'),
                    $request->input('start_timestamp'),
                    $request->input('end_timestamp')
                )->count(),
            'Registros consultados exitosamente',
            200
        );
    }

    public function roomUsagePercentageByDateRange(AvailableTimesBySpecializationRequest $request, $roomId)
    {
        $allRoomsUsed = $this->appointmentService->
            getFilterAppointmentsByDoctorAttributes(
                $request->input('doctor_id'),
                $request->input('specialization_id'),
                $request->input('start_timestamp'),
                $request->input('end_timestamp')
            );
        $specificRoomsUsed = $allRoomsUsed->where('room_id', $roomId);
        $usagePercentaje = ($specificRoomsUsed->count() * 100) / $allRoomsUsed->count();
        return SimpleJSONResponse::successResponse(
            [
                'usage_percentaje' => $usagePercentaje
            ],
            'Registros consultados exitosamente',
            200
        );
    }

    public function filterAppointmentsByDoctorAttributes(AvailableTimesBySpecializationRequest $request): JsonResponse
    {
        return SimpleJSONResponse::successResponse(
            $this->appointmentService->
                getFilterAppointmentsByDoctorAttributes(
                    $request->input('doctor_id'),
                    $request->input('specialization_id'),
                    $request->input('start_timestamp'),
                    $request->input('end_timestamp')
                ),
            'Registros consultados exitosamente',
            200
        );
    }

    public function availableTimesByDoctorAttributes(AvailableTimesBySpecializationRequest $request)
    {
        return SimpleJSONResponse::successResponse(
            $this->appointmentService
                ->getAvailableTimesByDoctorAttributes(
                    $request->input('doctor_id'),
                    $request->input('specialization_id'),
                    $request->input('start_timestamp'),
                    $request->input('end_timestamp')
                ),
            'Registros consultados exitosamente',
            200
        );
    }

    public function availableAppointmentsByDoctorAndRoom(AvailableTimesBySpecializationRequest $request)
    {
        return SimpleJSONResponse::successResponse(
            $this->appointmentService
                ->getAvailableAppointmentsByDoctorAndRoom(
                    $request->input('doctor_id'),
                    $request->input('specialization_id'),
                    $request->input('start_timestamp'),
                    $request->input('end_timestamp')
                ),
            'Registros consultados exitosamente',
            200
        );
    }
}