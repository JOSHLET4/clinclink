<?php

namespace App\Http\Controllers;

use App\Utils\SimpleJSONResponse;
use Illuminate\Http\Request;
use Str;

class SoftDeletedController extends Controller
{
    public function reactivateSoftDeleted(Request $request)
    {
        $request->validate(['id' => 'required|min:1']);
        $modelClass = 'App\\Models\\' . Str::studly($request->module);
        if (!class_exists($modelClass))
            return SimpleJSONResponse::errorResponse('Tabla no encontrada', 400);
        $record = $modelClass::withTrashed()->where('id', $request->id)->first();
        if ($record) {
            $record->restore();
            return SimpleJSONResponse::successResponse($record, 'Registro reactivo exitosamente. Actualiza los atriburtos', 200);
        }
        return SimpleJSONResponse::errorResponse('Registro no encontrado', 400);
    }
}
