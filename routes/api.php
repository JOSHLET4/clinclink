<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ConsultationController;
use App\Http\Controllers\DoctorDetailController;
use App\Http\Controllers\DoctorDetailSpecializationController;
use App\Http\Controllers\MedicalExamController;
use App\Http\Controllers\MedicalRecordController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\ModuleUserPermissionController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\SoftDeletedController;
use App\Http\Controllers\SpecializationController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WorkDayController;
use App\Http\Controllers\WorkHourController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::group(['middleware' => ['auth:sanctum']], function() {
    // rutas
    Route::get('user-profile', [AuthController::class, 'userProfile']);
    Route::get('logout', [AuthController::class, 'logout']);

    Route::post('reactive-record/{user}', [SoftDeletedController::class, 'reactivateSoftDeleted']);
    Route::post('all-soft-deleted-records', [SoftDeletedController::class, 'allSoftDeletedRecords']);
    Route::post('check-unique-attribute-in-records', [SoftDeletedController::class, 'checkUniqueAttributeInRecords']);

    // obtener tiempos libres de medicos segun fitro de atributos
    Route::post('appointment/available-times-by-doctor-attributes', [AppointmentController::class, 'availableAppointmentsByDoctorAttributes']);
    // obtener citas me doctor segun filtro de atributos
    Route::post('appointment/filter-appointments-by-doctor-attributes', [AppointmentController::class, 'filterByDoctorAttributesMethodRoute']);
    
    // ! (requiere revision) actualizar estado de cita
    Route::put('appointment/{appointment}/status', [AppointmentController::class, 'updateAppointmentStatus']);
    // ! (requiere revision) reprogramar citas
    Route::put('appointment/{appointment}/reschedule', [AppointmentController::class, 'rescheduleAppointment']);
    
    
    Route::post('appointment/count-by-date-range', [AppointmentController::class, 'countAppointmentsByDateRange']);
    Route::post('appointment/room/{roomId}/usage-percentage-by-date-range', [AppointmentController::class, 'roomUsagePercentageByDateRange']);


    Route::post('appointment/room/{roomId}/available-times-by-room-id', [RoomController::class, 'availableRoomsByRoomId']);

    
    // historia medica de paciente especifico, y doctor especifico si es necesario
    Route::post('/medical-records/{patientId}/history', [MedicalRecordController::class, 'patientHistory']);

    Route::resource('user', UserController::class);
    Route::resource('module', ModuleController::class);
    Route::resource('permission', PermissionController::class);
    Route::resource('module-user-permission', ModuleUserPermissionController::class);
    Route::resource('doctor-detail', DoctorDetailController::class);
    Route::resource('specialization', SpecializationController::class);
    Route::resource('doctor-detail-specialization', DoctorDetailSpecializationController::class);
    Route::resource('schedule', ScheduleController::class);
    Route::resource('medical-record', MedicalRecordController::class);

    // ! falta verificar la disponibilidad del medico antes de agregarlo (tanto citas como horario)
    Route::resource('appointment', AppointmentController::class);
    Route::resource('medical-exam', MedicalExamController::class);
    Route::resource('consultation', ConsultationController::class);
});
