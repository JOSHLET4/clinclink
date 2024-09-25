<?php

namespace App\Http\Controllers;

use App\Http\Requests\MedicalRecordRequest;
use App\Http\Requests\PatientHistoryRequest;
use App\Models\Consultation;
use App\Models\MedicalRecord;
use App\Utils\SimpleCRUD;
use App\Utils\SimpleJSONResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MedicalRecordController extends Controller
{
    public $crud;

    public function __construct()
    {
        $this->crud = new SimpleCRUD(new MedicalRecord);
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
    public function store(MedicalRecordRequest $request): JsonResponse
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
    public function update(MedicalRecordRequest $request, string $id): JsonResponse
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

    public function patientHistory(PatientHistoryRequest $request, $patientId)
    {
        $doctorId = $request->input('doctor_id');
        $data = Consultation::select([
            'medical_records.id as medical_record_id',
            'consultations.id as consultation_id',
            'doctors.id as doctor_id',
            'patients.id as patient_id',
            'patients.first_name as patient_name',
            'doctors.first_name as doctor_name',
            'consultations.diagnosis',
            'consultations.treatment',
            'consultations.notes'
        ])
        ->join('users as doctors', 'consultations.doctor_id', '=', 'doctors.id')
        ->join('medical_records', 'consultations.medical_record_id', '=', 'medical_records.id')
        ->join('users as patients', 'medical_records.patient_id', '=', 'patients.id')
        ->where('medical_records.patient_id', $patientId)
        ->when($doctorId, function ($query) use ($doctorId) {
                return $query->where('doctors.id', $doctorId);
            })
        ->get();
        return SimpleJSONResponse::successResponse(
            $data,
            'Registros consultados exitosamente',
            200
        );

    }
}
