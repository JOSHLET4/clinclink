<?php

use App\Http\Controllers\PdfController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// reporte appointments
Route::get('pdf/signed-appointment-url', [
    PdfController::class,
    'generateAppointmentReportSignedUrl'
])
    ->name('pdf.signed.appointment.url');

Route::get('pdf/download-appointment', [
    PdfController::class,
    'downloadAppointmentReport'
])
    ->name('pdf.download.appointment')
    ->middleware('signed');

// reporte de usuarios
Route::get('pdf/signed-user-url', [
    PdfController::class,
    'generateUserReportSignedUrl'
])
    ->name('pdf.signed.user.url');

Route::get('pdf/download-user', [
    PdfController::class,
    'downloadUserReport'
])
    ->name('pdf.download.user')
    ->middleware('signed');


