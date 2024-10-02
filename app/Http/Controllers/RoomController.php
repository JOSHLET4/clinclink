<?php

namespace App\Http\Controllers;

use App\Http\Requests\AvailableRoomsByRoomIdRequest;
use App\Models\Appointment;
use App\Services\RoomService;
use App\Utils\SimpleJSONResponse;
use DateTime;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function availableRoomsByRoomId(AvailableRoomsByRoomIdRequest $request, $roomId)
    {
        $roomService = new RoomService();
        return SimpleJSONResponse::successResponse(
            $roomService
                ->getAvailableRoomsByDateRange(
                    $roomId,
                    $request->input('start_timestamp'),
                    $request->input('end_timestamp')
                ),
            'Registros consultados exitosamente',
            200
        );
    }
}