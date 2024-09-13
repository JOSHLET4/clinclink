<?php
namespace App\Utils;

class SimpleJSONResponse
{
    public static function successResponse($data, $message, $statusCode)
    {
        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => $message,
        ], $statusCode);
    }
    public static function errorResponse($message, $statusCode)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], $statusCode);
    }
}