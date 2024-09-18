<?php
namespace App\Utils;

use App\Http\Resources\PaginatedResourceCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SimpleCRUD
{
    public $model;
    public function __construct($model)
    {
        $this->model = $model;
    }
    // verificar si contiene un resourceClass para aplicar o no serializacion
    public function validateResourceApplicability($resourceClass, $data)
    {
        return $resourceClass !== null ? new $resourceClass($data) : $data;
    }

    public function index($resourceClass = null, $paginationNumber = 0): JsonResponse
    {
        try {
            // validar si hay paginacion
            $validatePagination = $paginationNumber && $paginationNumber > 0;
            // consultar registros
            $data = $validatePagination
                ? $this->model::paginate($paginationNumber)
                : $this->model::all();
            // aplicar recurso
            if ($validatePagination && $resourceClass !== null)
                $data = new PaginatedResourceCollection($resourceClass, $data);
            if (!$validatePagination && $resourceClass !== null)
                $data = $resourceClass::collection($data);
            // respuesta
            return SimpleJSONResponse::successResponse(
                $data,
                ucfirst($this->model->getTable()) . ' consultados exitosamente',
                200
            );
        } catch (\Exception $e) {
            return SimpleJSONResponse::errorResponse(
                'Error al consultar ' . $this->model->getTable() . '. ' . $e->getMessage(),
                400
            );
        }
    }

    public function store(Request $request, $resourceClass = null): JsonResponse
    {
        try {
            $data = $this->model::create($request->all());
            $data = $this->model::find($data->id); // temporal
            $data = $this->validateResourceApplicability($resourceClass, $data);
            return SimpleJSONResponse::successResponse(
                $data,
                'Registro creado exitosamente en la tabla ' . $this->model->getTable(),
                200
            );
        } catch (\Exception $e) {
            return SimpleJSONResponse::errorResponse(
                'Error al crear registro en la tabla ' . $this->model->getTable() . ': ' . $e->getMessage(),
                400
            );
        }
    }

    public function show(string $id, $resourceClass = null): JsonResponse
    {
        try {
            $data = $this->model::find($id);
            $data = $this->validateResourceApplicability($resourceClass, $data);
            return SimpleJSONResponse::successResponse(
                $data,
                'Registro consultado exitosamente en la tabla ' . $this->model->getTable(),
                200
            );
        } catch (\Exception $e) {
            return SimpleJSONResponse::errorResponse(
                'Error al consultar registro en la tabla ' . $this->model->getTable() . ': ' . $e->getMessage(),
                400
            );
        }
    }

    public function update(Request $request, string $id, $resourceClass = null)
    {
        try {
            $data = $this->model::find($id);
            $data->update($request->all());
            $data = $this->model::find($data->id); // temporal
            $data = $this->validateResourceApplicability($resourceClass, $data);
            return SimpleJSONResponse::successResponse(
                $data,
                'Registro actualizado exitosamente en la tabla ' . $this->model->getTable(),
                200
            );
        } catch (\Exception $e) {
            return SimpleJSONResponse::errorResponse(
                'Error al actualizar registro en la tabla ' .$this->model->getTable() . ': ' . $e->getMessage(),
                400
            );
        }
    }

    public function destroy(string $id)
    {
        try {
            $this->model::find($id)->delete();
            return SimpleJSONResponse::successResponse(
                null,
                'Registro eliminado exitosamente en la tabla ' . $this->model->getTable(),
                200
            );
        } catch (\Exception $e) {
            return SimpleJSONResponse::errorResponse(
                'Error al eliminar registro en la tabla ' . $this->model->getTable() . ': ' . $e->getMessage(),
                400
            );
        }
    }
}