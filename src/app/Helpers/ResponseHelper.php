<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;

/**
 * Helper para estandarizar las respuestas JSON de la API.
 *
 * Proporciona métodos estáticos para construir respuestas consistentes
 * con estructura { message, data } y el código HTTP apropiado.
*/
class ResponseHelper
{
    /**
     * Retorna una respuesta JSON de éxito (HTTP 200).
     *
     * @param array  $data    Datos adicionales a incluir en la respuesta.
     * @param string $message Mensaje descriptivo de la operación.
     * @return JsonResponse
    */
    public static function success(array $data = [], string $message = 'Operación exitosa.'): JsonResponse
    {
        return self::custom($message, $data, 200);
    }

    /**
     * Retorna una respuesta JSON de creación exitosa (HTTP 201).
     *
     * @param array  $data    Datos del recurso creado.
     * @param string $message Mensaje descriptivo de la operación.
     * @return JsonResponse
    */
    public static function created(array $data = [], string $message = 'Recurso creado exitosamente.'): JsonResponse
    {
        return self::custom($message, $data, 201);
    }

    /**
     * Retorna una respuesta JSON aceptada para procesamiento asíncrono (HTTP 202).
     *
     * Útil cuando la solicitud fue recibida pero su procesamiento
     * se realizará de forma diferida (colas, jobs, webhooks, etc).
     *
     * @param array  $data    Datos adicionales a incluir en la respuesta.
     * @param string $message Mensaje descriptivo de la operación.
     * @return JsonResponse
    */
    public static function accepted(array $data = [], string $message = 'Solicitud aceptada para su procesamiento.'): JsonResponse
    {
        return self::custom($message, $data, 202);
    }

    /**
     * Retorna una respuesta JSON sin contenido (HTTP 204).
     *
     * @return JsonResponse
    */
    public static function noContent(): JsonResponse
    {
        return response()->json(null, 204);
    }

    /**
     * Retorna una respuesta JSON de solicitud incorrecta (HTTP 400).
     *
     * @param string $message Mensaje de error.
     * @param array  $errors  Detalles adicionales del error (opcional).
     * @return JsonResponse
    */
    public static function badRequest(string $message = 'Solicitud incorrecta.', array $errors = []): JsonResponse
    {
        return self::custom($message, $errors, 400);
    }

    /**
     * Retorna una respuesta JSON de no autorizado (HTTP 401).
     *
     * @param string $message Mensaje de error.
     * @return JsonResponse
    */
    public static function unauthorized(string $message = 'Acceso no autorizado.'): JsonResponse
    {
        return self::custom($message, [], 401);
    }

    /**
     * Retorna una respuesta JSON de prohibido (HTTP 403).
     *
     * @param string $message Mensaje de error.
     * @return JsonResponse
    */
    public static function forbidden(string $message = 'Acceso prohibido.'): JsonResponse
    {
        return self::custom($message, [], 403);
    }

    /**
     * Retorna una respuesta JSON de recurso no encontrado (HTTP 404).
     *
     * @param string $message Mensaje de error.
     * @return JsonResponse
    */
    public static function notFound(string $message = 'Recurso no encontrado.'): JsonResponse
    {
        return self::custom($message, [], 404);
    }

    /**
     * Retorna una respuesta JSON de método no permitido (HTTP 405).
     *
     * @param string $message Mensaje de error.
     * @return JsonResponse
    */
    public static function methodNotAllowed(string $message = 'Método no permitido.'): JsonResponse
    {
        return self::custom($message, [], 405);
    }

    /**
     * Retorna una respuesta JSON de conflicto (HTTP 409).
     *
     * @param string $message Mensaje de error.
     * @param array  $data    Datos adicionales del conflicto (opcional).
     * @return JsonResponse
    */
    public static function conflict(string $message = 'Conflicto con el estado actual del recurso.', array $data = []): JsonResponse
    {
        return self::custom($message, $data, 409);
    }

    /**
     * Retorna una respuesta JSON de error de validación (HTTP 422).
     *
     * @param string $message Mensaje de error general.
     * @param array  $errors  Errores de validación por campo.
     * @return JsonResponse
    */
    public static function validationError(string $message = 'Error de validación.', array $errors = []): JsonResponse
    {
        return self::custom($message, $errors, 422);
    }

    /**
     * Retorna una respuesta JSON de demasiadas solicitudes (HTTP 429).
     *
     * @param string   $message    Mensaje de error.
     * @param int|null $retryAfter Segundos sugeridos antes de reintentar (opcional).
     * @return JsonResponse
    */
    public static function tooManyRequests(string $message = 'Demasiadas solicitudes. Intenta nuevamente más tarde.', ?int $retryAfter = null): JsonResponse
    {
        $response = self::custom($message, [], 429);

        if ($retryAfter !== null) {
            $response->header('Retry-After', (string) $retryAfter);
        }

        return $response;
    }

    /**
     * Retorna una respuesta JSON de error interno del servidor (HTTP 500).
     *
     * @param string $message Mensaje de error.
     * @return JsonResponse
    */
    public static function criticalError(string $message = 'Error crítico del servidor.'): JsonResponse
    {
        return self::custom($message, [], 500);
    }

    /**
     * Retorna una respuesta JSON de servicio no disponible (HTTP 503).
     *
     * @param string $message Mensaje de error.
     * @return JsonResponse
    */
    public static function serviceUnavailable(string $message = 'Servicio no disponible.'): JsonResponse
    {
        return self::custom($message, [], 503);
    }

    /**
     * Retorna una respuesta JSON que contiene únicamente los datos,
     * sin el envoltorio de "message" ni estructura adicional.
     *
     * @param array $data Datos a devolver directamente como cuerpo de la respuesta.
     * @param int   $code Código HTTP de respuesta.
     * @return JsonResponse
    */
    public static function raw(array $data = [], int $code = 200): JsonResponse
    {
        return response()->json($data, $code);
    }

    /**
     * Método centralizado para construir una respuesta JSON personalizada.
     *
     * @param string $message Mensaje de la respuesta.
     * @param array  $data    Datos a devolver.
     * @param int    $code    Código HTTP de respuesta.
     * @return JsonResponse
    */
    public static function custom(string $message, array $data, int $code): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'data'    => $data,
        ], $code);
    }
}