<?php

namespace App\Http\Controllers;

use App\Utils\SimpleJSONResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Str;

class SoftDeletedController extends Controller
{
    public function reactivateSoftDeleted(Request $request, string $id): JsonResponse
    {
        $request->validate(['module' => 'required']);
        $modelClass = 'App\\Models\\' . Str::studly($request->module);
        if (!class_exists($modelClass))
            return SimpleJSONResponse::errorResponse(
                'Tabla no encontrada',
                400
            );
        $record = $modelClass::withTrashed()->where('id', $id)->first();
        if ($record) {
            $record->restore();
            return SimpleJSONResponse::successResponse(
                $record,
                'Registro reactivo exitosamente',
                200
            );
        }
        return SimpleJSONResponse::errorResponse('Registro no encontrado', 400);
    }
    public function allSoftDeletedRecords(Request $request): JsonResponse
    {
        $request->validate(['module' => 'required']);
        $modelClass = 'App\\Models\\' . Str::studly($request->module);
        $modelInstance = new $modelClass;
        if (!class_exists($modelClass))
            return SimpleJSONResponse::errorResponse(
                'Tabla no encontrada',
                400
            );
        $data = $modelClass::onlyTrashed()->get();
        return SimpleJSONResponse::successResponse(
            $data,
            ucfirst($modelInstance->getTable()) . ' consultados exitosamente',
            200
        );
    }
    public function checkUniqueAttributeInRecords(Request $request)
    {
        $request->validate([
            'module' => 'required|min:1',
            'attributeName' => 'required|min:1',
            'attributeValue' => 'required|min:1'
        ]);
        $request->validate(['module' => 'required']);
        $modelClass = 'App\\Models\\' . Str::studly($request->module);
        $modelInstance = new $modelClass;
        if (!class_exists($modelClass))
            return SimpleJSONResponse::errorResponse(
                'Tabla no encontrada',
                400
            );
        $data = $modelClass::withTrashed()->where(
            $request->attributeName,
            $request->attributeValue
        )->first();
        if ($data && $data->trashed()) {
            return response()->json([
                'recordId' => $data->id,
                'foundAttribute' => true,
                'removedAttribute' => true,
                'data' => $data,
                'message' => $request->attributeName . ' ya esta registrado y ha sido eliminado'
            ], 200);
        }
        if ($data) {
            return response()->json([
                'recordId' => $data->id,
                'foundAttribute' => true,
                'removedAttribute' => false,
                'data' => $data,
                'message' => $request->attributeName . ' ya esta registrado y no esta eliminado'
            ], 200);
        }
        return response()->json([
            'foundAttribute' => false,
            'removedAttribute' => false,
            'data' => $data,
            'message' => $request->attributeName . ' no esta registrado'
        ], 200);
    }
}
