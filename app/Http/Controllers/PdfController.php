<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Utils\SimpleJSONResponse;
use Barryvdh\DomPDF\Facade\Pdf;
use DateTime;
use URL;

class PdfController extends Controller
{
    public function generateAppointmentReportSignedUrl()
    {
        // creacion de ruta firmada y protegida
        $signedUrl = URL::temporarySignedRoute(
            'pdf.download.appointment', // Nombre de la ruta protegida
            now()->addMinutes(10), // Tiempo de expiración de 10 minutos
            []
        );
        // retorno de ruta firmada
        return SimpleJSONResponse::successResponse(
            ['signedUrl' => $signedUrl],
            'url de descarga de pdf generada exitosamente',
            200
        );
    }

    public function generateUserReportSignedUrl()
    {
        $signedUrl = URL::temporarySignedRoute(
            'pdf.download.user',
            now()->addMinutes(10), 
            []
        );
        return SimpleJSONResponse::successResponse(
            ['signedUrl' => $signedUrl],
            'url de descarga de pdf generada exitosamente',
            200
        );
    }

    public function downloadUserReport()
    {
        $data = User::select('*')->get();
        $pdf = PDF::loadView('pdf.user-report', ['data' => $data]);
        return $pdf->download('user-report.pdf');
    }

    public function downloadAppointmentReport()
    {
        // si la validacion de la ruta firmada es correcta se aplicara la logica
        $currentDate = new DateTime();
        $firstDate = (clone $currentDate)->modify('first day of this month');
        $lastDate = (clone $currentDate)->modify('last day of this month');
        $appointmentController = new AppointmentController();
        $data = $appointmentController->filterAppointmentsByDoctorAttributes(
            null,
            null,
            $firstDate,
            $lastDate,
        );
        $pdf = PDF::loadView('pdf.appointment-report', ['data' => $data]);
        return $pdf->download('appointment-report.pdf');
    }
}
