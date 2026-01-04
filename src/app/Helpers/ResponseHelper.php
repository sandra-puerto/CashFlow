<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;

/**
 * Clase Helper para estandarizar respuestas HTTP comunes.
*/
class ResponseHelper
{
    /**
     * Devuelve una respuesta JSON estandarizada para éxito (HTTP 200).
     *
     * @param array $data Datos a devolver.
     * @param string $message Mensaje opcional.
     * @return \Illuminate\Http\JsonResponse
    */
    public static function success(array $data = [], string $message = 'Operación exitosa.'): JsonResponse
    {
        return self::custom($message, $data, 200);
    }

    /**
     * Devuelve una respuesta JSON estandarizada para el código HTTP 500 (Error del Servidor).
     *
     * @param string $message Mensaje de error a mostrar.
     * @return \Illuminate\Http\JsonResponse
    */
    public static function criticalError(string $message = 'Error crítico del servidor.'): JsonResponse
    {
        return self::custom($message, [], 500);
    }

    /**
     * Devuelve una respuesta JSON estandarizada para el código HTTP 401 (Unauthorized).
     *
     * @param string $message Mensaje de error a mostrar.
     * @return \Illuminate\Http\JsonResponse
    */
    public static function unauthorized(string $message = 'Acceso no autorizado.'): JsonResponse
    {
        return self::custom($message, [], 401);
    }

    /**
     * Devuelve una respuesta JSON estandarizada para el código HTTP 404 (Not Found).
     *
     * @param string $message Mensaje de error a mostrar.
     * @return \Illuminate\Http\JsonResponse
    */
    public static function notFound(string $message = 'Recurso no encontrado.'): JsonResponse
    {
        return self::custom($message, [], 404);
    }

    /**
     * Método centralizado para construir la respuesta JSON.
     *
     * @param string|null $message Mensaje de la respuesta.
     * @param array $data Datos a devolver.
     * @param int $code Código HTTP de respuesta.
     * @return \Illuminate\Http\JsonResponse
    */
    public static function custom(?string $message, array $data, int $code): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'data' => $data
        ], $code);
    }
}