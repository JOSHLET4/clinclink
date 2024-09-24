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

    Route::post('appointment/available-appointments-by-specialization', [AppointmentController::class, 'availableAppointmentsBySpecialization']);
    Route::post('appointment/appointments-by-specialization', [AppointmentController::class, 'appointmentsBySpecialization']);

    Route::resource('user', UserController::class);
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
