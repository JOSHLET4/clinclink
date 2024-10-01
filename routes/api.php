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
use App\Http\Controllers\RoleController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\SoftDeletedController;
use App\Http\Controllers\SpecializationController;
use App\Http\Controllers\UserController;
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

    // obtener citas de doctor segun filtro de atributos
    Route::post('appointment/filter-appointments-by-doctor-attributes', [AppointmentController::class, 'filterAppointmentsByDoctorAttributesRouteMethod']);
    
    // cambiar estado de una cita
    Route::put('appointment/{appointment}/status', [AppointmentController::class, 'updateAppointmentStatus']);
    // cambiar programacion de tiempos de una cita
    Route::put('appointment/{appointment}/reschedule', [AppointmentController::class, 'rescheduleAppointment']);
    
    // obtener el conteo las citas programads por rango de tiempos
    Route::post('appointment/count-by-date-range', [AppointmentController::class, 'countAppointmentsByDateRange']);
    
    // obtener porcentaje de uso de cuarto especifico por rango de tiempo horario de 0:00:00 a 23:59:59 horas
    Route::post('appointment/room/{roomId}/usage-percentage-by-date-range', [AppointmentController::class, 'roomUsagePercentageByDateRange']);

    // obtener disponibilidad de tiempos para un cuarto especifico en un rango de tiempo
    // ? metodo en RoomController para mayor control y no sobrecargar AppointmentController
    Route::post('appointment/room/{roomId}/available-times-by-room-id', [RoomController::class, 'availableRoomsByRoomId']);

    // historia medica de paciente especifico, y doctor especifico si es necesario
    Route::post('/medical-records/{patientId}/history', [MedicalRecordController::class, 'patientHistory']);

    Route::resource('user', UserController::class);
    Route::resource('role', RoleController::class);
    Route::resource('module', ModuleController::class);
    Route::resource('permission', PermissionController::class);
    Route::resource('module-user-permission', ModuleUserPermissionController::class);
    Route::resource('doctor-detail', DoctorDetailController::class);
    Route::resource('specialization', SpecializationController::class);
    Route::resource('doctor-detail-specialization', DoctorDetailSpecializationController::class);
    Route::resource('schedule', ScheduleController::class);
    Route::resource('medical-record', MedicalRecordController::class);
    Route::resource('appointment', AppointmentController::class);
    Route::resource('medical-exam', MedicalExamController::class);
    Route::resource('consultation', ConsultationController::class);
});
