<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Http\Requests\AuthRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;

class AuthController extends Controller
{
    /**
     * Valida las credenciales del usuario e intenta emitir un token JWT.
     *
     * En caso de éxito, devuelve el token. En caso de credenciales inválidas o
     * fallos de sistema, devuelve una respuesta de error 401 para evitar
     * la enumeración de usuarios (Blind Failure).
     *
     * @param \App\Http\Requests\AuthRequest $request
     * @return \Illuminate\Http\JsonResponse
    */
    public function login(AuthRequest $request)
    {
        try {
            $credentials = $request->only('email', 'password');

            if ($token = Auth::attempt($credentials)) {
                return $this->respondWithToken($token);
            }
            
        } catch (\Exception | JWTException $exception) {

            /* En modo debug, devolver el mensaje de error detallado. */
            if (env('APP_DEBUG')) {
                return ResponseHelper::criticalError($exception->getMessage());
            }

            Log::error("Error en Autenticación: " . $exception->getMessage());
        }

        return ResponseHelper::unauthorized('Credenciales inválidas.');
    }

    /**
     * Retorna una respuesta JSON estructurada con el token de acceso,
     * el tipo de token y el tiempo de expiración.
     *
     * @param string $token
     * @return \Illuminate\Http\JsonResponse
    */
    private function respondWithToken(string $token)
    {
        return ResponseHelper::success([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::factory()->getTTL() * 60
        ]);
    }

    /**
     * Cierra la sesión del usuario actual.
     *
     * @return \Illuminate\Http\JsonResponse
    */
    public function logout()
    {
        try {
            Auth::logout();
            return ResponseHelper::success([], 'Sesión cerrada exitosamente.');

        } catch (TokenBlacklistedException $e) {
            return ResponseHelper::unauthorized('La sesión ya fue cerrada.');

        } catch (\Exception $e) {
            Log::error("Error al cerrar sesión: " . $e->getMessage());

            /* En modo debug, devolver el mensaje de error detallado. */
            if (env('APP_DEBUG')) {
                return ResponseHelper::criticalError($e->getMessage());
            }
        }
    }
}